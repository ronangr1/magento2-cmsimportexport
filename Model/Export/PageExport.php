<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model\Export;

use Magento\Cms\Api\Data\PageInterface;
use Ronangr1\CmsImportExport\Model\Exporter;

class PageExport extends Exporter
{
    protected array $headers = [
        PageInterface::PAGE_ID, PageInterface::TITLE, PageInterface::IDENTIFIER, PageInterface::PAGE_LAYOUT,
        PageInterface::IS_ACTIVE, PageInterface::CONTENT_HEADING, PageInterface::META_KEYWORDS, PageInterface::META_DESCRIPTION,
        PageInterface::META_TITLE, PageInterface::CONTENT, PageInterface::CREATION_TIME, PageInterface::UPDATE_TIME, PageInterface::SORT_ORDER,
        PageInterface::LAYOUT_UPDATE_XML, PageInterface::CUSTOM_THEME, PageInterface::CUSTOM_ROOT_TEMPLATE,
        PageInterface::CUSTOM_LAYOUT_UPDATE_XML, PageInterface::CUSTOM_THEME_FROM, PageInterface::CUSTOM_THEME_TO,
    ];
}
