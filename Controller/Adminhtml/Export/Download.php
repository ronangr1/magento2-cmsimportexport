<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;

class Download extends Action
{
    public function execute()
    {
        $file = $this->getRequest()->getParam('pathToFile');
        if ($file) {
            $file = base64_decode($file);
            if (file_exists($file)) {
                $filename = basename($file);
                $this->_actionFlag->set('', 'no-dispatch', true);
                $this->getResponse()->setHttpResponseCode(200);
                $this->getResponse()->setHeader('Content-Type', 'application/zip');
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $filename);
                $this->getResponse()->setBody(file_get_contents($file));
                unlink($file);
            }
        } else {
            $this->messageManager->addErrorMessage(__('File not found.'));
            return $this->_redirect('cms/**/*');
        }
    }
}
