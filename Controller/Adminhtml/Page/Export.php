<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml\Page;

use Ronangr1\CmsImportExport\Controller\Adminhtml\Exporter;

class Export extends Exporter
{
    protected string $type = "cms_page";
}
