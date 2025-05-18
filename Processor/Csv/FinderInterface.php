<?php

namespace Ronangr1\CmsImportExport\Processor\Csv;

interface FinderInterface
{
    public function findCsvFiles(string $dir): array;
}
