<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Model\Export;

use Magento\Cms\Api\Data\BlockInterface;
use Ronangr1\CmsImportExport\Model\Exporter;

class BlockExport extends Exporter
{
    public array $headers = [
        BlockInterface::BLOCK_ID, BlockInterface::TITLE, BlockInterface::IDENTIFIER,
        BlockInterface::CONTENT, BlockInterface::CREATION_TIME, BlockInterface::UPDATE_TIME,
        BlockInterface::IS_ACTIVE
    ];
}
