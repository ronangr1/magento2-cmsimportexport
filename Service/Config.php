<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Service;

use Magento\Store\Api\Data\StoreConfigInterface;

class Config
{
    public function __construct(
        protected readonly StoreConfigInterface $storeConfig
    )
    {
    }

    public function addMedia(): true
    {
        /** @TODO Create system config */
        return true;
    }
}
