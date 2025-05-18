<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model\Archive;

use FilesystemIterator;
use Magento\Framework\Archive\Zip as MagentoZip;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Zip extends MagentoZip
{
    public function pack($source, $destination): string
    {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException(sprintf("Unable to create %s to archive", $destination));
        }

        if (is_dir($source)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $item) {
                $realPath = $item->getRealPath();
                $local = ltrim(str_replace($source, "", $realPath), DIRECTORY_SEPARATOR);
                if ($item->isDir()) {
                    $zip->addEmptyDir($local);
                } elseif ($item->isFile()) {
                    $zip->addFile($realPath, $local);
                }
            }
        } else {
            parent::pack($source, $destination);
        }

        $zip->close();

        return $destination;
    }

    public function unpack($source, $destination): string
    {
        $zip = new ZipArchive();
        if ($zip->open($source) !== true) {
            throw new \RuntimeException(sprintf("Unable to open the archive '%s'", $source));
        }

        $zip->extractTo($destination);
        $zip->close();

        return $destination;
    }
}
