<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Processor\Csv;

use PHPUnit\Framework\TestCase;
use Ronangr1\CmsImportExport\Processor\Csv\Reader;

class ReaderTest extends TestCase
{
    private $tempFile;

    protected function tearDown(): void
    {
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testReadCsvRowSuccess()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($this->tempFile, "col1,col2\nfoo,bar\n");
        $reader = new Reader();
        $result = $reader->readCsvRow($this->tempFile);
        $this->assertSame(['col1' => 'foo', 'col2' => 'bar'], $result);
    }

    public function testReadCsvRowThrowsOnNonExistingFile()
    {
        $reader = new Reader();
        $this->expectException(\RuntimeException::class);
        $reader->readCsvRow('/not/exist.csv');
    }

    public function testReadCsvRowThrowsOnInvalidCsv()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($this->tempFile, "id,name\n1\n");
        $reader = new Reader();
        $this->expectException(\RuntimeException::class);
        $reader->readCsvRow($this->tempFile);
    }

    public function testReadCsvRowThrowsOnUnreadableFile()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($this->tempFile, "id,name\n1,foo\n");
        chmod($this->tempFile, 0000);
        $reader = new Reader();
        $this->expectException(\RuntimeException::class);
        $reader->readCsvRow($this->tempFile);
    }
}
