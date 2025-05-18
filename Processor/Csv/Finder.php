<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Processor\Csv;

class Finder implements FinderInterface
{
    public function findCsvFiles(string $dir): array
    {
        $files = glob(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "*.csv") ?: [];
        return array_values(array_filter($files, function ($path) {
            return is_file($path) && is_readable($path);
        }));
    }
}
