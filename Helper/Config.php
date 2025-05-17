<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig,
    )
    {
    }

    public function allowDownloadMedia(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cmsimportexport/media/allow_download',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    public function allowOverwrite(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cmsimportexport/general/allow_overwrite',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
