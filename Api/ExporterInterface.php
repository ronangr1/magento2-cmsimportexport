<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Api;

interface ExporterInterface
{
    public function export(int $id, string $type);
}
