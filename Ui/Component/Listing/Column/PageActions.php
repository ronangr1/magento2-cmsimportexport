<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Ui\Component\Listing\Column;

use Magento\Cms\Ui\Component\Listing\Column\PageActions as MagentoPageActions;

class PageActions extends MagentoPageActions
{
    private const URL_PATH_EXPORT = 'cmsimportexport/page/export';

    public function prepareDataSource(array $dataSource): array
    {
        $dataSource = parent::prepareDataSource($dataSource);
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &  $item) {
                if (isset($item['page_id'])) {
                    $item[$this->getData('name')]['export'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_EXPORT,
                            [
                                'id' => $item['page_id']
                            ]
                        ),
                        'label' => __('Export')
                    ];
                }
            }
        }

        return $dataSource;
    }
}
