<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive\ArchiveInterface;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Ronangr1\CmsImportExport\Api\ExporterInterface;
use Ronangr1\CmsImportExport\Helper\Config;

class Exporter implements ExporterInterface
{
    protected array $headers = [];

    public function __construct(
        private readonly PageRepositoryInterface  $pageRepository,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly DirectoryList $directoryList,
        private readonly IoFile $ioFile,
        private readonly FilterProvider $filterProvider,
        private readonly Config $config,
        private readonly ArchiveInterface $zip,
    )
    {
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export(int $id, string $type): array
    {
        $fp = fopen('php://memory', 'w+');
        if ($fp === false) {
            throw new \RuntimeException('Unable to open memory stream for CSV generation.');
        }

        $entity = $this->exportEntityCsv($type, $id, $fp);

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $exportDir = $varDir . '/export';
        $this->ioFile->mkdir($exportDir, 0755);

        $tmpDir = $exportDir . '/tmp_' . uniqid();
        $this->ioFile->mkdir($tmpDir, 0755);

        $csvFileName = sprintf('%s_%d.csv', $type, $id);
        $csvFilePath = $tmpDir . '/' . $csvFileName;
        file_put_contents($csvFilePath, $csvContent);

        if ($this->config->allowDownloadMedia()) {
            $this->exportEntityMedia($entity, $tmpDir);
        }

        $zipName = sprintf('%s_%d_export.zip', $type, $id);
        $zipPath = $exportDir . '/' . $zipName;
        $this->zip->pack($tmpDir, $zipPath);

        $this->ioFile->rmdir($tmpDir, true);

        return [
            $zipName,
            [
                'type' => 'filename',
                'value' => $zipPath,
                'rm' => true
            ],
            'application/zip'
        ];
    }

    private function exportEntityCsv(string $type, int $id, $fp)
    {
        $config = [
            'cms_page' => [
                'repository' => $this->pageRepository,
                'fields' => [
                    'getId',
                    'getTitle',
                    'getIdentifier',
                    'getPageLayout',
                    'isActive',
                    'getContentHeading',
                    'getMetaKeywords',
                    'getMetaDescription',
                    'getMetaTitle',
                    'getContent',
                    'getCreationTime',
                    'getUpdateTime',
                    'getSortOrder',
                    'getLayoutUpdateXml',
                    'getCustomTheme',
                    'getCustomRootTemplate',
                    'getCustomLayoutUpdateXml',
                    'getCustomThemeFrom',
                    'getCustomThemeTo',
                ],
            ],
            'cms_block' => [
                'repository' => $this->blockRepository,
                'fields' => [
                    'getId',
                    'getTitle',
                    'getIdentifier',
                    'isActive',
                    'getContent',
                    'getCreationTime',
                    'getUpdateTime'
                ],
            ],
        ];

        if (!isset($config[$type])) {
            throw new \InvalidArgumentException(sprintf('Unknown entity type "%s".', $type));
        }

        $repo = $config[$type]['repository'];
        $fields = $config[$type]['fields'];
        $entity = $repo->getById($id);

        fputcsv($fp, $this->headers);

        $row = array_map(
            function (string $getter) use ($entity) {
                return $entity->$getter();
            },
            $fields
        );
        fputcsv($fp, $row);

        return $entity;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function exportEntityMedia($entity, string $tmpDir): void
    {
        $content = html_entity_decode($entity->getContent());
        $filtered = $this->filterProvider->getPageFilter()->filter($content);

        if (preg_match_all('/<img[^>]+src="([^"]+)"/i', $filtered, $matches)) {
            $mediaTmpDir = $tmpDir . '/media';
            $this->ioFile->mkdir($mediaTmpDir, 0755);

            $mediaBase = $this->directoryList->getPath(DirectoryList::MEDIA);
            $srcs = array_unique($matches[1]);

            foreach ($srcs as $src) {
                if (!str_contains($src, '/media/')) {
                    continue;
                }
                $path = parse_url($src, PHP_URL_PATH) ?: '';
                $relative = ltrim(preg_replace('#^/media/#', '', $path), '/');
                $absolute = $mediaBase . DIRECTORY_SEPARATOR . $relative;
                if (is_file($absolute)) {
                    $this->ioFile->cp($absolute, $mediaTmpDir . '/' . basename($absolute));
                }
            }
        }
    }
}
