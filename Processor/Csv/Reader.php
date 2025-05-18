<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Processor\Csv;

class Reader
{
    public function readCsvRow(string $filePath): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException(sprintf("Failed to read file: %s", $filePath));
        }

        $h = fopen($filePath, "r");
        if ($h === false) {
            throw new \RuntimeException(sprintf("Failed to open file: %s", $filePath));
        }

        $headers = fgetcsv($h);
        $data = fgetcsv($h);
        fclose($h);

        if (!$data || count($headers) !== count($data)) {
            throw new \RuntimeException(sprintf("Failed to read headers: %s", implode(", ", $headers)));
        }

        return array_combine($headers, $data);
    }
}
