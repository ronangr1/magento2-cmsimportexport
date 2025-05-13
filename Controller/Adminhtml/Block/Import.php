<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml\Block;

use Magento\Framework\Controller\ResultFactory;
use Ronangr1\CmsImportExport\Controller\Adminhtml\Importer;

class Import extends Importer
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $result->getConfig()->getTitle()->set('Import Blocks');

        return $result;
    }
}
