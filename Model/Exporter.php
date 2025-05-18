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
use Ronangr1\CmsImportExport\Api\ExporterInterface;
use Ronangr1\CmsImportExport\Processor\Csv\ExporterInterface as CsvExporter;
use Ronangr1\CmsImportExport\Processor\Media;
use Ronangr1\CmsImportExport\Service\Config;

class Exporter implements ExporterInterface
{
    public array $headers = [];

    public function __construct(
        private readonly CsvExporter $csvExporter,
        private readonly Media $media,
        private readonly DirectoryList $directoryList,
        private readonly IoFile $ioFile,
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
        $fp = fopen("php://memory", "w+");
        if ($fp === false) {
            throw new \RuntimeException("Unable to open memory stream for CSV generation.");
        }

        $headers = $this->headers;
        $entity = $this->csvExporter->exportEntityCsv($type, $id, $fp, $headers);

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $exportDir = $varDir . "/export";
        $this->ioFile->mkdir($exportDir, 0755);

        $tmpDir = $exportDir . "/tmp_" . $this->getUniqId();
        $this->ioFile->mkdir($tmpDir, 0755);

        $csvFileName = sprintf("%s_%d.csv", $type, $id);
        $csvFilePath = $tmpDir . "/" . $csvFileName;
        $this->writeToFile($csvFilePath, $csvContent);

        if ($this->config->allowDownloadMedia()) {
            $this->media->exportEntityMedia($entity, $tmpDir);
        }

        $zipName = sprintf("%s_%d_export.zip", $type, $id);
        $zipPath = $exportDir . "/" . $zipName;
        $this->zip->pack($tmpDir, $zipPath);

        $this->ioFile->rmdir($tmpDir, true);

        return [
            $zipName,
            [
                "type" => "filename",
                "value" => $zipPath,
                "rm" => true
            ],
            "application/zip"
        ];
    }

    protected function writeToFile(string $filename, string $data): int
    {
        return file_put_contents($filename, $data);
    }

    protected function getUniqId(): string
    {
        return uniqid();
    }
}
