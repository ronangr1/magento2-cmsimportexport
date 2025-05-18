<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ronangr1\CmsImportExport\Model\Importer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Archive\ArchiveInterface;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Ronangr1\CmsImportExport\Processor\Csv\FinderInterface;
use Ronangr1\CmsImportExport\Processor\Csv\ReaderInterface;
use Ronangr1\CmsImportExport\Processor\Entity;
use Ronangr1\CmsImportExport\Processor\Media;
use Ronangr1\CmsImportExport\Service\Config;

class ImporterTest extends TestCase
{
    private MockObject $entity;

    private MockObject $directoryList;

    private MockObject $zip;

    private MockObject $ioFile;

    private MockObject $entityProcessor;

    private MockObject $finderInterface;

    private MockObject $reader;

    private MockObject $media;

    private MockObject $config;

    public function setUp(): void
    {
        $this->entity = $this->createMock(\stdClass::class);
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->zip = $this->createMock(ArchiveInterface::class);
        $this->ioFile = $this->createMock(IoFile::class);
        $this->entityProcessor = $this->createMock(Entity::class);
        $this->finderInterface = $this->createMock(FinderInterface::class);
        $this->reader = $this->createMock(ReaderInterface::class);
        $this->media  = $this->createMock(Media::class);
        $this->config = $this->createMock(Config::class);
    }

    public function testImportProcess()
    {
        $zipPath = "/tmp/file.zip";
        $type = "cms_page";
        $varDir = "/var/www/var";
        $baseName = "file";
        $importDir = $varDir . "/import/" . $baseName;
        $csvPath = $importDir . "/my-file.csv";
        $csvRow = ["identifier" => "foo", "content" => "bar"];
        $entityMock = $this->entity;

        $directoryList = $this->directoryList;
        $directoryList->method("getPath")->willReturn($varDir);

        $ioFile = $this->ioFile;
        $ioFile->method("fileExists")->willReturn(true);

        $archive = $this->zip;

        $finder = $this->finderInterface;
        $finder->method("findCsvFiles")->with($importDir)->willReturn([$csvPath]);

        $csvReader = $this->reader;
        $csvReader->method("readCsvRow")->with($csvPath)->willReturn($csvRow);

        $entityProcessor = $this->entityProcessor;
        $entityProcessor->method("buildEntityFromRow")->with($type, $csvRow)->willReturn($entityMock);
        $entityProcessor->expects($this->once())->method("save")->with($type, $entityMock);

        $media = $this->media;
        $media->expects($this->never())->method("importMediaFromDir");

        $config = $this->config;
        $config->method("allowDownloadMedia")->willReturn(false);

        $importer = new Importer(
            $entityProcessor,
            $csvReader,
            $directoryList,
            $media,
            $ioFile,
            $config,
            $archive,
            $finder
        );

        $importer->import($zipPath, $type);
    }

    public function testImportThrowsWhenNoCsvFound()
    {
        $zipPath = "/tmp/file.zip";
        $type = "cms_page";
        $varDir = "/var/www/var";
        $baseName = "file";
        $importDir = $varDir . "/import/" . $baseName;

        $directoryList = $this->directoryList;
        $directoryList->method("getPath")->willReturn($varDir);

        $ioFile = $this->ioFile;
        $ioFile->method("fileExists")->willReturn(false);

        $archive = $this->zip;

        $finder = $this->finderInterface;
        $finder->method("findCsvFiles")->with($importDir)->willReturn([]);

        $csvReader = $this->reader;
        $entityProcessor = $this->entityProcessor;
        $media = $this->media;
        $config = $this->config;

        $importer = new Importer(
            $entityProcessor, $csvReader, $directoryList, $media,
            $ioFile, $config, $archive, $finder
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("No CSV file found in");

        $importer->import($zipPath, $type);
    }

    public function testImportThrowsWhenExtractFails()
    {
        $zipPath = "/tmp/file.zip";
        $type = "cms_page";
        $varDir = "/path/to/var";
        $baseName = "file";
        $importDir = $varDir . "/import/" . $baseName;

        $directoryList = $this->directoryList;
        $directoryList->method("getPath")->willReturn($varDir);

        $ioFile = $this->ioFile;
        $ioFile->method("fileExists")->willReturn(false);
        $ioFile->expects($this->once())->method("mkdir")->with($importDir, 0755);

        $archive = $this->zip;
        $archive->method("unpack")->willThrowException(new \Exception("BAD ZIP"));

        $finder = $this->finderInterface;
        $csvReader = $this->reader;
        $entityProcessor = $this->entityProcessor;
        $media = $this->media;
        $config = $this->config;

        $importer = new Importer(
            $entityProcessor, $csvReader, $directoryList, $media,
            $ioFile, $config, $archive, $finder
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Failed to extract archive: BAD ZIP");

        $importer->import($zipPath, $type);
    }
}
