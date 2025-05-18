<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Processor;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class Media
{
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly FilterProvider $filterProvider,
        private readonly File $ioFile,
    )
    {
    }

    public function importMediaFromDir(string $importDir, string $content): string
    {
        $mediaDir = $importDir . "/media";
        if (!is_dir($mediaDir)) {
            return $content;
        }

        $pubMedia = $this->directoryList->getPath(DirectoryList::MEDIA);
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($mediaDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            $rel = substr((string)$item, strlen($mediaDir) + 1);
            $dest = $pubMedia . DIRECTORY_SEPARATOR . $rel;
            if ($item->isDir()) {
                $this->ioFile->mkdir(pathinfo($dest, PATHINFO_DIRNAME), 0755);
            } else {
                $this->ioFile->cp((string)$item, $dest);
            }
        }

        return preg_replace_callback(
            "~(['\"]?)/media/([^'\")\s]+)(['\"]?)~i",
            fn($m) => "{{media url='" . $m[2] . "'}}",
            $content
        );
    }

    public function exportEntityMedia($entity, string $tmpDir): void
    {
        $content = html_entity_decode($entity->getContent());
        $filtered = $this->filterProvider->getPageFilter()->filter($content);
        if (preg_match_all('/<img[^>]+src="([^"]+)"/i', $filtered, $matches)) {
            $mediaTmpDir = $tmpDir . "/media";
            $this->ioFile->mkdir($mediaTmpDir, 0755);

            $mediaBase = $this->directoryList->getPath(DirectoryList::MEDIA);
            $srcs = array_unique($matches[1]);

            foreach ($srcs as $src) {
                if (!str_contains($src, "/media/")) {
                    continue;
                }
                $path = parse_url($src, PHP_URL_PATH) ?: "";
                $relative = ltrim(preg_replace("#^/media/#", "", $path), "/");
                $absolute = $mediaBase . DIRECTORY_SEPARATOR . $relative;
                $this->ioFile->cp($absolute, $mediaTmpDir . "/" . basename($absolute));
            }
        }
    }
}
