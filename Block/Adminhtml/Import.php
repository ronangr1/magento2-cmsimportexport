<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Import extends Template
{
    public function getActionUrl(): string
    {
        return $this->getUrl('cmsimportexport/ajax/import', ['_secure' => true]);
    }
}
