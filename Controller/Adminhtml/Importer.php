<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

abstract class Importer extends Action
{
    public const ADMIN_RESOURCE = 'Ronangr1_CmsImportExport::import';

    protected string $template = 'Ronangr1_CmsImportExport::import.phtml';

    public function __construct(
        Context $context,
    )
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $result->getConfig()->getTitle()->set('Import');

        return $result;
    }
}
