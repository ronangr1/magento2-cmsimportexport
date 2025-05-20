<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Ronangr1\CmsImportExport\Api\ImporterInterface;

class Import extends Action
{
    public function __construct(
        Context $context,
        private readonly ImporterInterface $importer,
    )
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $params = $this->getRequest()->getParams();
        if (!$params) {
            $result->setData([
                "success" => false,
                "message" => "Import failed"
            ]);

            return $result;
        }

        try {
            $zipFile = $this->getRequest()->getFiles("import_file");
            $this->importer->import($zipFile, $this->getRequest()->getParam("entity_type"));

            $result->setData([
                "success" => true,
                "message" => "Import completed"
            ]);
        } catch (\Exception $e) {
            $result->setData([
                "success" => false,
                "message" => "Import failed: " . $e->getMessage()
            ]);
        }

        return $result;
    }
}
