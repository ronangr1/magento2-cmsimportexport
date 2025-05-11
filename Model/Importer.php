<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Archive\ArchiveInterface;
use Ronangr1\CmsImportExport\Api\ImporterInterface;
use Ronangr1\CmsImportExport\Service\Config;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File as IoFile;

class Importer implements ImporterInterface
{
    private $buildEntityFromRow;

    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly BlockFactory $blockFactory,
        private readonly GetPageByIdentifierInterface $pageByIdentifier,
        private readonly PageRepositoryInterface $pageRepository,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly DirectoryList $directoryList,
        private readonly IoFile $ioFile,
        private readonly Config $config,
        private readonly ArchiveInterface $zip,
    )
    {
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function import(string $zipFilePath, string $type): void
    {
        $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $baseName = pathinfo($zipFilePath, PATHINFO_FILENAME);
        $importDir = $varDir . '/import/' . $baseName;
        if ($this->ioFile->fileExists($importDir, false)) {
            $this->ioFile->rmdir($importDir, true);
        }
        $this->ioFile->mkdir($importDir, 0755);

        try {
            $this->zip->unpack($zipFilePath, $importDir);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Failed to extract archive: %s', $e->getMessage()));
        }

        $all = glob($importDir . '/*.csv');
        $csvFiles = array_filter($all, function($path) {
            return is_file($path) && is_readable($path);
        });
        if (empty($csvFiles)) {
            throw new \RuntimeException(sprintf("No CSV file found in '%s'.", $zipFilePath));
        }
        $csvPath = reset($csvFiles);

        $row = $this->readCsvRow($csvPath);

        $entity = $this->buildEntityFromRow($type, $row);

        if ($this->config->addMedia()) {
            $newContent = $this->importMediaFromDir($importDir, $row['content'] ?? '');
            $entity->setContent($newContent);
        }

        $this->saveEntity($type, $entity);

        $this->ioFile->rmdir($importDir, true);
    }

    private function readCsvRow(string $filePath): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException(sprintf('Failed to read file: %s', $filePath));
        }

        $h = fopen($filePath, 'r');
        if ($h === false) {
            throw new \RuntimeException(sprintf('Failed to open file: %s', $filePath));
        }

        $headers = fgetcsv($h);
        $data    = fgetcsv($h);
        fclose($h);

        if (!$data || count($headers) !== count($data)) {
            throw new \RuntimeException(sprintf('Failed to read headers: %s', implode(', ', $headers)));
        }

        return array_combine($headers, $data);
    }

    private function buildEntityFromRow(string $type, array $row)
    {
        $config = [
            'cms_page' => [
                'loader' => function (array $r) {
                    try {
                        return $this->pageByIdentifier->execute($r['identifier'] ?? '', 0);
                    } catch (\Exception $e) {
                        return $this->pageFactory->create();
                    }
                },
                'fields' => [
                    'title' => 'setTitle',
                    'identifier' => 'setIdentifier',
                    'is_active' => 'setIsActive',
                    'content_heading' => 'setContentHeading',
                    'meta_keywords' => 'setMetaKeywords',
                    'meta_description' => 'setMetaDescription',
                ],
                'saver' => function ($entity) {
                    $this->pageRepository->save($entity);
                }
            ],
            'cms_block' => [
                'loader' => function (array $r) {
                    $identifier = $r['identifier'] ?? '';
                    $block = $this->blockFactory->create();
                    if ($identifier) {
                        $block->load($identifier, 'identifier');
                    }
                    return $block;
                },
                'fields' => [
                    'title' => 'setTitle',
                    'identifier' => 'setIdentifier',
                    'is_active' => 'setIsActive',
                ],
                'saver' => function ($entity) {
                    $this->blockRepository->save($entity);
                }
            ],
        ];

        if (!isset($config[$type])) {
            throw new \InvalidArgumentException("Type inconnu « $type »");
        }
        $c = $config[$type];
        $entity = $c['loader']($row);

        foreach ($c['fields'] as $fieldKey => $setter) {
            if (array_key_exists($fieldKey, $row)) {
                $value = $row[$fieldKey];
                $entity->$setter($value);
            }
        }

        if (!$this->config->addMedia() && isset($row['content'])) {
            $entity->setContent($row['content']);
        }

        return $entity;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function importMediaFromDir(string $importDir, string $content): string
    {
        $mediaDir = $importDir . '/media';
        if (!is_dir($mediaDir)) {
            return $content;
        }

        $pubMedia = $this->directoryList->getPath(DirectoryList::MEDIA);
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($mediaDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            $rel = substr((string)$item, strlen($mediaDir) + 1);
            $dest = $pubMedia . DIRECTORY_SEPARATOR . $rel;
            if ($item->isDir()) {
                $this->ioFile->mkdir(pathinfo($dest, PATHINFO_DIRNAME), 0755);
            } else {
                $this->ioFile->cp((string)$item, $dest);
            }
        }

        return preg_replace_callback(
            '~(["\']?)/media/([^"\')\s]+)(["\']?)~i',
            fn($m) => '{{media url="' . $m[2] . '"}}',
            $content
        );
    }

    private function saveEntity(string $type, $entity): void
    {
        $config = [
            'cms_page' => fn($e) => $this->pageRepository->save($e),
            'cms_block' => fn($e) => $this->blockRepository->save($e),
        ];
        if (!isset($config[$type])) {
            throw new \InvalidArgumentException("Type inconnu « $type »");
        }
        try {
            $config[$type]($entity);
        } catch (LocalizedException $e) {
            throw new \RuntimeException("Unable to save $type: " . $e->getMessage());
        }
    }
}
