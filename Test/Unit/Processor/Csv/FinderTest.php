<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Processor\Csv;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Ronangr1\CmsImportExport\Processor\Csv\Finder;

class FinderTest extends TestCase
{
    private $directoryRead;
    private $finder;

    protected function setUp(): void
    {
        $this->directoryRead = $this->createMock(ReadInterface::class);

        $filesystemStub = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystemStub->method("getDirectoryReadByPath")->willReturn($this->directoryRead);

        $this->finder = new Finder($filesystemStub);
    }

    public function testFindCsvFiles()
    {
        $dir = "/path/to/import/dir";

        $this->directoryRead->method("read")
            ->with($dir)
            ->willReturn([
                "a.csv",
                "b.csv",
                "c.txt",
                "d.csv"
            ]);

        $this->directoryRead->method("isFile")->willReturnMap([
            ["a.csv", true],
            ["b.csv", true],
            ["c.txt", true],
            ["d.csv", true],
        ]);

        $this->directoryRead->method("isReadable")->willReturnMap([
            ["a.csv", true],
            ["b.csv", true],
            ["c.txt", true],
            ["d.csv", false],
        ]);

        $files = $this->finder->findCsvFiles($dir);

        $this->assertEquals(["a.csv", "b.csv"], $files);
    }
}
