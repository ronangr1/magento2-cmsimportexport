<?php

namespace Ronangr1\CmsImportExport\Processor\Csv;

interface ReaderInterface
{
    public function readCsvRow(string $path): array;
}
