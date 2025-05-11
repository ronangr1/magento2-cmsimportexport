<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model\Export;

use Ronangr1\CmsImportExport\Model\Exporter;

class PageExport extends Exporter
{
    protected array $headers = [
        "page_id", "title", "identifier", "is_active",
        "content_heading", "meta_keywords", "meta_description", "content"
    ];
}
