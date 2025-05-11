<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Block\Adminhtml\Block\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\View\Element\UiComponent\Context;

class ExportButton implements ButtonProviderInterface
{

    public function __construct(
        private readonly Context $context
    )
    {
    }

    public function getButtonData(): array
    {
        $blockId = (int) $this->context->getRequestParam('block_id');

        return [
            'label' => __('Export'),
            'on_click' => "location.href = '{$this->getExportUrl($blockId)}';",
            'class' => 'secondary',
            'sort_order' => 90,
        ];
    }

    private function getExportUrl(?int $blockId): string
    {
        return $this->context->getUrl(
            'cmsimportexport/block/export',
            ['id' => $blockId]
        );
    }
}
