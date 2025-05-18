<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive\ArchiveInterface;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Ronangr1\CmsImportExport\Api\ImporterInterface;
use Ronangr1\CmsImportExport\Processor\Csv\FinderInterface;
use Ronangr1\CmsImportExport\Processor\Csv\Reader;
use Ronangr1\CmsImportExport\Processor\Entity;
use Ronangr1\CmsImportExport\Processor\Media;
use Ronangr1\CmsImportExport\Service\Config;

class Importer implements ImporterInterface
{
    public function __construct(
        private readonly Entity $processor,
        private readonly Reader $csvReader,
        private readonly DirectoryList $directoryList,
        private readonly Media $media,
        private readonly IoFile $ioFile,
        private readonly Config $config,
        private readonly ArchiveInterface $zip,
        private readonly FinderInterface $csvFinder,
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
        $importDir = $varDir . "/import/" . $baseName;
        if ($this->ioFile->fileExists($importDir, false)) {
            $this->ioFile->rmdir($importDir, true);
        }
        $this->ioFile->mkdir($importDir, 0755);

        try {
            $this->zip->unpack($zipFilePath, $importDir);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf("Failed to extract archive: %s", $e->getMessage()));
        }

        $csvFiles = $this->csvFinder->findCsvFiles($importDir);
        if (empty($csvFiles)) {
            throw new \RuntimeException(sprintf("No CSV file found in '%s'.", $zipFilePath));
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
