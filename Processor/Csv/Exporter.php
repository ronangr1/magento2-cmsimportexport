<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Processor\Csv;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;

class Exporter implements ExporterInterface
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly BlockRepositoryInterface $blockRepository,
    )
    {
    }

    public function exportEntityCsv(string $type, int $id, $fp, array $headers): array|PageInterface|BlockInterface
    {
        $config = [
            "cms_page" => [
                "repository" => $this->pageRepository,
                "fields" => [
                    "getId",
                    "getTitle",
                    "getIdentifier",
                    "getPageLayout",
                    "isActive",
                    "getContentHeading",
                    "getMetaKeywords",
                    "getMetaDescription",
                    "getMetaTitle",
                    "getContent",
                    "getCreationTime",
                    "getUpdateTime",
                    "getSortOrder",
                    "getLayoutUpdateXml",
                    "getCustomTheme",
                    "getCustomRootTemplate",
                    "getCustomLayoutUpdateXml",
                    "getCustomThemeFrom",
                    "getCustomThemeTo",
                ],
            ],
            "cms_block" => [
                "repository" => $this->blockRepository,
                "fields" => [
                    "getId",
                    "getTitle",
                    "getIdentifier",
                    "isActive",
                    "getContent",
                    "getCreationTime",
                    "getUpdateTime"
                ],
            ],
        ];

        if (!isset($config[$type])) {
            throw new \InvalidArgumentException(sprintf("Unknown entity type '%s'.", $type));
        }

        $repo = $config[$type]["repository"];
        $fields = $config[$type]["fields"];
        $entity = $repo->getById($id);

        fputcsv($fp, $headers);

        $row = array_map(
            function (string $getter) use ($entity) {
                return $entity->$getter();
            },
            $fields
        );
        fputcsv($fp, $row);

        return $entity;
    }
}
