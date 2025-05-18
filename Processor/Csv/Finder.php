<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Processor\Csv;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

class Finder implements FinderInterface
{
    private ReadInterface $directoryRead;

    public function __construct(
        Filesystem $filesystem
    )
    {
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
    }

    public function findCsvFiles(string $dir): array
    {
        $parts = explode('/', $dir);
        $sanitizedParts = array_slice($parts, -2);
        $dir = implode('/', $sanitizedParts);
        $csvFiles = [];
        $files = $this->directoryRead->read($dir);
        foreach ($files as $file) {
            if (
                $this->directoryRead->isFile($file) &&
                $this->directoryRead->isReadable($file) &&
                str_ends_with($file, '.csv')
            ) {
                $csvFiles[] = $file;
            }
        }
        return $csvFiles;
    }
}
