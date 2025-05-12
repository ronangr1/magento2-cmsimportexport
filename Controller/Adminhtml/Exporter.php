<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Ronangr1\CmsImportExport\Api\ExporterInterface;

abstract class Exporter extends Action
{
    public const ADMIN_RESOURCE = 'Ronangr1_CmsImportExport::export';

    protected string $type = "cms_default";

    public function __construct(
        Action\Context $context,
        protected readonly FileFactory $fileFactory,
        protected readonly ExporterInterface $exporter
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        [$fileName, $content, $mime] = $this->exporter->export($id, $this->type);
        return $this->fileFactory->create(
            $fileName, $content, DirectoryList::VAR_DIR, $mime
        );
    }
}
