<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;
use Ronangr1\CmsImportExport\Processor\Csv\Reader;

class ReaderTest extends TestCase
{
    private MockObject $directoryRead;
    private MockObject $fileStream;
    private $reader;

    protected function setUp(): void
    {
        $this->directoryRead = $this->createMock(ReadInterface::class);
        $this->fileStream = $this->createMock(FileReadInterface::class);
        $this->reader = new Reader($this->directoryRead);
    }

    public function testReadCsvRowReturnsAssocRow()
    {
        $path = 'import/file.csv';

        $this->directoryRead->method('isFile')->with($path)->willReturn(true);
        $this->directoryRead->method('isReadable')->with($path)->willReturn(true);
        $this->directoryRead->method('openFile')->with($path, 'r')->willReturn($this->fileStream);
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
        $this->directoryRead->method('isFile')->with($path)->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->reader->readCsvRow($path);
    }

    public function testThrowsOnHeadersMismatch()
    {
        $path = 'import/file.csv';

        $this->directoryRead->method('isFile')->with($path)->willReturn(true);
        $this->directoryRead->method('isReadable')->with($path)->willReturn(true);
        $this->directoryRead->method('openFile')->with($path, 'r')->willReturn($this->fileStream);
        $this->fileStream->expects($this->exactly(2))->method('readCsv')->willReturnOnConsecutiveCalls(
            ['id', 'identifier'],
            ['onlyOneValue']
        );
        $this->fileStream->expects($this->once())->method('close');

        $this->expectException(\RuntimeException::class);
        $this->reader->readCsvRow($path);
    }
}
