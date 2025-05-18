<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Processor;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Ronangr1\CmsImportExport\Service\Config;

class Entity
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly GetBlockByIdentifierInterface $blockByIdentifier,
        private readonly GetPageByIdentifierInterface $pageByIdentifier,
        private readonly BlockFactory $blockFactory,
        private readonly PageFactory $pageFactory,
        private readonly Config $config,
    )
    {
    }

    public function save(string $type, $entity): void
    {
        $config = [
            "cms_page" => fn($e) => $this->pageRepository->save($e),
            "cms_block" => fn($e) => $this->blockRepository->save($e),
        ];
        if (!isset($config[$type])) {
            throw new \InvalidArgumentException(sprintf("Unknown entity type: %s", $type));
        }
        try {
            $config[$type]($entity);
        } catch (LocalizedException $e) {
            throw new \RuntimeException(sprintf("Failed to save entity: %s", $e->getMessage()));
        }
    }

    public function buildEntityFromRow(string $type, array $row)
    {
        $config = [
            "cms_page" => [
                "loader" => function (array $row) {
                    if (empty($row["identifier"])) {
                        throw new LocalizedException(__("Page identifier is required."));
                    }

                    $identifier = $row["identifier"];

                    try {
                        $page = $this->pageByIdentifier->execute($identifier, Store::DEFAULT_STORE_ID);
                        $allowOverwrite = $this->config->allowOverwrite();
                        if ($page->getId() && !$allowOverwrite) {
                            throw new LocalizedException(__("Page with identifier '%1' already exists.", $identifier));
                        }
                    } catch (NoSuchEntityException) {
                        $page = $this->pageFactory->create();
                    } catch (\Exception $e) {
                        throw new LocalizedException(__("Failed to load page: %1", $e->getMessage()));
                    }

                    return $page;
                },
                "fields" => [
                    "title" => "setTitle",
                    "identifier" => "setIdentifier",
                    "is_active" => "setIsActive",
                    "content_heading" => "setContentHeading",
                    "meta_keywords" => "setMetaKeywords",
                    "meta_description" => "setMetaDescription",
                ],
                "saver" => function ($entity) {
                    $this->pageRepository->save($entity);
                }
            ],
            "cms_block" => [
                "loader" => function (array $row) {
                    if (empty($row["identifier"])) {
                        throw new LocalizedException(__("Block identifier is required."));
                    }

                    $identifier = $row["identifier"];

                    try {
                        $block = $this->blockByIdentifier->execute($identifier, Store::DEFAULT_STORE_ID);
                        if ($block->getId() && !$this->config->allowOverwrite()) {
                            throw new LocalizedException(__("Block with identifier '%1' already exists.", $identifier));
                        }
                    } catch (NoSuchEntityException $e) {
                        $block = $this->blockFactory->create();
                    } catch (\Exception $e) {
                        throw new LocalizedException(__("Failed to load block: %1", $e->getMessage()));
                    }

                    return $block;
                },
                "fields" => [
                    "title" => "setTitle",
                    "identifier" => "setIdentifier",
                    "is_active" => "setIsActive",
                ],
                "saver" => function ($entity) {
                    $this->blockRepository->save($entity);
                }
            ],
        ];

        if (!isset($config[$type])) {
            throw new \InvalidArgumentException(sprintf("Unknown type: %s", $type));
        }

        $c = $config[$type];
        $entity = $c["loader"]($row);

        foreach ($c["fields"] as $fieldKey => $setter) {
            if (array_key_exists($fieldKey, $row)) {
                $value = $row[$fieldKey];
                $entity->$setter($value);
            }
        }

        if (!$this->config->allowDownloadMedia() && isset($row["content"])) {
            $entity->setContent($row["content"]);
        }

        return $entity;
    }
}
