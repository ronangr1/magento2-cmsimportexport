<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Ronangr1\CmsImportExport\Api\ExporterInterface;

abstract class Exporter extends Action
{
    public const ADMIN_RESOURCE = "Ronangr1_CmsImportExport::export";

    protected string $type = "cms_default";

    public function __construct(
        Action\Context  $context,
        protected readonly FileFactory $fileFactory,
        protected readonly ExporterInterface $exporter,
        protected readonly RedirectFactory $redirectFactory,
        protected readonly PageRepositoryInterface $pageRepository,
        protected readonly BlockRepositoryInterface $blockRepository,
        protected readonly UrlInterface $url,
        protected $entityRepository = null,
    )
    {
        parent::__construct($context);
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam("id");
        if ($id === 0) {
            $this->messageManager->addErrorMessage(__("You must save the entity before exporting."));
            return $this->redirectFactory->create()->setPath("cms/*/new");
        }

        [$fileName, $content, $mime] = $this->exporter->export($id, $this->getEntityType());

        $this->fileFactory->create(
            $fileName, $content, DirectoryList::VAR_DIR, $mime
        );

        $zipUrl = $this->url->getUrl(
            'cmsimportexport/export/download',
            [
                'pathToFile' => base64_encode($content["value"]),
                '_secure' => true
            ]
        );

        $phrases = "";

        try {
            $entity = $this->getInstance()->getById($id);
            $phrases .= __(
                'The %3 "%2" has been exported with success! (<a href="%1" target="_blank">Download the archive</a>)<br/>',
                $zipUrl,
                $entity->getTitle(),
                $this->getEntityTitle()
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        if ($phrases) {
            $this->messageManager->addComplexSuccessMessage("withHtml", [
                "html" => $phrases,
                "allowed_tags" => ["a", "br"],
            ]);
        }

        return $this->redirectFactory->create()->setPath("cms/*/index");
    }

    protected function getEntityTitle(): string
    {
        return $this->type === "cms_page" ? "page" : "block";
    }

    protected function getEntityType(): string
    {
        return $this->type;
    }

    protected function getInstance(): BlockRepositoryInterface|PageRepositoryInterface
    {
        if ($this->entityRepository === null) {
            match ($this->getEntityTYpe()) {
                "cms_page" => $this->entityRepository = $this->pageRepository,
                "cms_block" => $this->entityRepository = $this->blockRepository,
                default => throw new LocalizedException(__("Invalid entity type")),
            };
        }
        return $this->entityRepository;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
