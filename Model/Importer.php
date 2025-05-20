<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive\ArchiveInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Ronangr1\CmsImportExport\Api\ImporterInterface;
use Ronangr1\CmsImportExport\Processor\Csv\FinderInterface;
use Ronangr1\CmsImportExport\Processor\Csv\ReaderInterface;
use Ronangr1\CmsImportExport\Processor\Entity;
use Ronangr1\CmsImportExport\Processor\Media;
use Ronangr1\CmsImportExport\Service\Config;

class Importer implements ImporterInterface
{
    public function __construct(
        private readonly Entity $processor,
        private readonly ReaderInterface $csvReader,
        private readonly DirectoryList $directoryList,
        private readonly Media $media,
        private readonly IoFile $ioFile,
        private readonly Config $config,
        private readonly ArchiveInterface $zip,
        private readonly FinderInterface $csvFinder,
        private readonly UploaderFactory $uploaderFactory,
    )
    {
    }

    public function import(array $zipFile, string $type): void
    {
        try {
            $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
            $importDir = $varDir . "/import/" . $type;
            $uploader = $this->uploaderFactory->create(['fileId' => 'import_file']);
            $uploader->save($importDir);
            $filePath = $importDir . '/' . $zipFile['full_path'];
            $this->zip->unpack($filePath, $importDir);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf("Failed to extract archive: %s", $e->getMessage()));
        }

        $csvFiles = $this->csvFinder->findCsvFiles($importDir);
        if (empty($csvFiles)) {
            throw new \RuntimeException(sprintf("No CSV file found in '%s'.", $zipFile['full_path']));
        }
        $csvPath = reset($csvFiles);

        $row = $this->csvReader->readCsvRow($csvPath);
        $entity = $this->processor->buildEntityFromRow($type, $row);

        if ($this->config->allowDownloadMedia()) {
            $newContent = $this->media->importMediaFromDir($importDir, $row["content"] ?? "");
            $entity->setContent($newContent);
        }

        $this->processor->save($type, $entity);

        $this->ioFile->rmdir($importDir, true);
    }
}
