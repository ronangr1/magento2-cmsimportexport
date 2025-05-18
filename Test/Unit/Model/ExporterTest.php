<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ronangr1\CmsImportExport\Model\Exporter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive\ArchiveInterface;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Ronangr1\CmsImportExport\Processor\Csv\ExporterInterface as CsvExporter;
use Ronangr1\CmsImportExport\Processor\Media;
use Ronangr1\CmsImportExport\Service\Config;

class ExporterTest extends TestCase
{
    private MockObject $directoryList;

    private MockObject $ioFile;

    private MockObject $csvExporter;

    private MockObject $media;

    private MockObject $config;

    private MockObject $zip;

    protected function setUp(): void
    {
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->ioFile = $this->createMock(IoFile::class);
        $this->csvExporter = $this->createMock(CsvExporter::class);
        $this->media = $this->createMock(Media::class);
        $this->config = $this->createMock(Config::class);
        $this->zip = $this->createMock(ArchiveInterface::class);
    }

    public function testExportProcessNominal()
    {
        $type = "cms_page";
        $id = 42;
        $varDir = "/path/to/var/";
        $exportDir = $varDir . "/export";
        $unique = "abcd1234";
        $tmpDir = "$exportDir" . "/tmp_" . $unique;
        $csvFileName = $type . "_" . $id . ".csv";
        $csvFilePath = $tmpDir . "/" . $csvFileName;
        $zipName = $type . "_". $id . "_export.zip";
        $zipPath = $exportDir . "/" .$zipName;

        $directoryList = $this->directoryList;
        $directoryList->expects($this->once())
            ->method("getPath")
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($varDir);

        $ioFile = $this->ioFile;
        $ioFile->expects($this->exactly(2))->method("mkdir");
        $ioFile->expects($this->once())->method("rmdir")->with($tmpDir, true);

        $entity = ["id" => "123"];
        $csvExporter = $this->csvExporter;
        $csvExporter
            ->expects($this->once())
            ->method("exportEntityCsv")
            ->with(
                $type,
                $id,
                $this->callback("is_resource"),
                $this->anything()
            )
            ->willReturnCallback(function($type, $id, $fp, $headers) use ($entity) {
                if (is_resource($fp)) {
                    fwrite($fp, "id,name\n42,testpage\n");
                }
                return $entity;
            });


        $media = $this->media;
        $media->expects($this->never())->method("exportEntityMedia");

        $config = $this->config;
        $config->expects($this->once())->method("allowDownloadMedia")->willReturn(false);

        $zip = $this->zip;
        $zip->expects($this->once())->method("pack")->with($tmpDir, $zipPath);

        $exporter = $this->getMockBuilder(Exporter::class)
            ->setConstructorArgs([
                $csvExporter,
                $media,
                $directoryList,
                $ioFile,
                $config,
                $zip,
            ])
            ->onlyMethods(["writeToFile", "getUniqId"])
            ->getMock();

        $exporter->expects($this->once())
            ->method("getUniqId")
            ->willReturn($unique);

        $exporter->expects($this->once())
            ->method("writeToFile")
            ->with($csvFilePath, $this->isType("string"))
            ->willReturnCallback(function ($filename, $data) {
                $this->assertStringContainsString(".csv", $filename);
                $this->assertNotEmpty($data);
                return strlen($data);
            });

        $result = $exporter->export($id, $type);

        $this->assertSame(
            [
                $zipName,
                [
                    "type"  => "filename",
                    "value" => $zipPath,
                    "rm"    => true,
                ],
                "application/zip",
            ],
            $result
        );
    }
}
