<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;

abstract class MassExporter extends Exporter
{

    public function execute()
    {
        $ids = $this->getRequest()->getParam("selected");
        if(!$ids) {
            $this->messageManager->addErrorMessage(__("You must select ids before exporting."));
            return $this->redirectFactory->create()->setPath("cms/*/index");
        }

        $phrases = "";

        foreach ($ids as $id) {
            $id = (int) $id;
            [$fileName, $content, $mime] = $this->exporter->export($id, $this->getEntityTYpe());

            $this->fileFactory->create(
                $fileName, $content, DirectoryList::VAR_DIR, $mime
            );

            $zipUrl = $this->getUrl(
                'cmsimportexport/export/download',
                [
                    'pathToFile' => base64_encode($content["value"]),
                    '_secure'    => true
                ]
            );

            try {
                $cms = $this->getInstance()->getById($id);
                $phrases .= __(
                    'The %3 "%2" has been exported with success! (<a href="%1" target="_blank">Download the archive</a>)<br/>',
                    $zipUrl,
                    $cms->getTitle(),
                    $this->getEntityTitle()
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if($phrases) {
            $this->messageManager->addComplexSuccessMessage("withHtml", [
                "html" => $phrases,
                "allowed_tags" => ["a", "br"],
            ]);
        }

        return $this->redirectFactory->create()->setPath("cms/*/index");
    }
}
