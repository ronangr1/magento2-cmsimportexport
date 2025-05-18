<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Block\Adminhtml\Page\Edit;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\View\Element\UiComponent\Context;

class ExportButton implements ButtonProviderInterface
{
    private const ACL_RESOURCE_EXPORT = "Ronangr1_CmsImportExport::export";

    public function __construct(
        private readonly Context $context,
        private readonly AuthorizationInterface $authorization
    )
    {
    }

    public function getButtonData(): array
    {
        if (!$this->isAllowed()) {
            return [];
        }

        $pageId = (int) $this->context->getRequestParam("page_id");

        return [
            "label" => __("Export"),
            "on_click" => "location.href = '{$this->getExportUrl($pageId)};",
            "class" => "secondary",
            "sort_order" => 90,
        ];
    }

    private function getExportUrl(?int $pageId): string
    {
        return $this->context->getUrl(
            "cmsimportexport/page/export",
            ["id" => $pageId]
        );
    }

    public function isAllowed(): bool
    {
        return $this->authorization->isAllowed(self::ACL_RESOURCE_EXPORT);
    }
}
