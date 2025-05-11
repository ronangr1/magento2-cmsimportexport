<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Block\Adminhtml\Page\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\View\Element\UiComponent\Context;

class ExportButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getButtonData(): array
    {
        $pageId = (int) $this->context->getRequestParam('page_id');

        return [
            'label' => __('Export'),
            'on_click' => "location.href = '{$this->getExportUrl($pageId)}';",
            'class' => 'secondary',
            'sort_order' => 90,
        ];
    }

    private function getExportUrl(?int $pageId): string
    {
        return $this->context->getUrl(
            'cmsimportexport/page/export',
            ['id' => $pageId]
        );
    }
}
