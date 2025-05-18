<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;
use Ronangr1\CmsImportExport\Processor\Csv\Reader;

class ReaderTest extends TestCase
{
    private MockObject $filesystem;
    private MockObject $directoryRead;
    private MockObject $fileStream;
    private Reader $reader;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->directoryRead = $this->createMock(ReadInterface::class);
        $this->fileStream = $this->createMock(FileReadInterface::class);

        $this->filesystem->method('getDirectoryRead')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->directoryRead);

        $this->reader = new Reader($this->filesystem, $this->directoryRead);
    }

    public function testReadCsvRowReturnsAssocRow()
    {
        $path = 'import/file.csv';

        $this->directoryRead->method('isExist')->with($this->anything())->willReturn(true);
        $this->directoryRead->method('isReadable')->with($this->anything())->willReturn(true);
        $this->directoryRead->method('isFile')->with($this->anything())->willReturn(true);
        $this->directoryRead->method('openFile')->with($this->anything(), 'r')->willReturn($this->fileStream);

        $this->fileStream->expects($this->exactly(2))->method('readCsv')->willReturnOnConsecutiveCalls(
            ['id', 'identifier'],
            ['1', 'dummy']
        );
        $this->fileStream->expects($this->once())->method('close');
        $result = $this->reader->readCsvRow($path);

        $this->assertEquals(['id' => '1', 'identifier' => 'dummy'], $result);
    }

    public function testThrowsOnUnreadableFile()
    {
        $path = 'import/missing.csv';
        $this->directoryRead->method('isExist')->with($path)->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->reader->readCsvRow($path);
    }

    public function testThrowsOnHeadersMismatch()
    {
        $path = 'import/file.csv';

        $this->directoryRead->method('isExist')->with($this->anything())->willReturn(true);
        $this->directoryRead->method('isReadable')->with($this->anything())->willReturn(true);
        $this->directoryRead->method('isFile')->with($this->anything())->willReturn(true);
        $this->directoryRead->method('openFile')->with($this->anything(), 'r')->willReturn($this->fileStream);
        $this->fileStream->expects($this->exactly(2))->method('readCsv')->willReturnOnConsecutiveCalls(
            ['id', 'identifier'],
            ['onlyOneValue']
        );
        $this->fileStream->expects($this->once())->method('close');

        $this->expectException(\RuntimeException::class);
        $this->reader->readCsvRow($path);
    }
}
