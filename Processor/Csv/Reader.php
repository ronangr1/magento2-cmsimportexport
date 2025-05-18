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

class Reader implements ReaderInterface
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ReadInterface $directoryRead,
    )
    {
    }

    public function readCsvRow(string $path): array
    {
        $varDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $file = $varDirectory->getAbsolutePath($path);

        if (
            !$this->directoryRead->isFile($file) ||
            !$this->directoryRead->isReadable($file)
        ) {
            throw new \RuntimeException(sprintf("Failed to read file: %s", $file));
        }

        $stream = $this->directoryRead->openFile($file, 'r');
        $headers = $stream->readCsv();
        $data = $stream->readCsv();
        $stream->close();

        if (!$data || count($headers) !== count($data)) {
            throw new \RuntimeException(sprintf("Failed to read headers: %s", implode(", ", $headers ?? [])));
        }

        return array_combine($headers, $data);
    }
}
