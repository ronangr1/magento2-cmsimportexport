<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Processor\Csv;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ronangr1\CmsImportExport\Processor\Csv\Exporter;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\PageInterface;

class ExporterTest extends TestCase
{
    private MockObject $pageRepository;

    private MockObject $blockRepository;

    private $exporter;

    private $entity;

    protected function setUp(): void
    {
        $this->pageRepository = $this->createMock(PageRepositoryInterface::class);
        $this->blockRepository = $this->createMock(BlockRepositoryInterface::class);
        $this->entity = $this->createMock(PageInterface::class);
        $this->exporter = new Exporter(
            $this->pageRepository,
            $this->blockRepository
        );
    }

    public function testExportEntityCsvPage()
    {
        $type = "cms_page";
        $id   = 42;
        $headers = ["id", "title", "identifier", "page_layout", "is_active", "content_heading", "meta_keywords", "meta_description", "meta_title", "content", "creation_time", "update_time", "sort_order", "layout_update_xml", "custom_theme", "custom_root_template", "custom_layout_update_xml", "custom_theme_from", "custom_theme_to"];

        $entity = $this->entity;
        $entity->method("getId")->willReturn(42);
        $entity->method("getTitle")->willReturn("Titre Exemple");
        $entity->method("getIdentifier")->willReturn("test-identifier");
        $entity->method("getPageLayout")->willReturn("1column");
        $entity->method("isActive")->willReturn(true);
        $entity->method("getContentHeading")->willReturn("");
        $entity->method("getMetaKeywords")->willReturn("");
        $entity->method("getMetaDescription")->willReturn("");
        $entity->method("getMetaTitle")->willReturn("");
        $entity->method("getContent")->willReturn("Hello World");
        $entity->method("getCreationTime")->willReturn("2023-01-01 00:00:00");
        $entity->method("getUpdateTime")->willReturn("2023-01-01 00:00:00");
        $entity->method("getSortOrder")->willReturn(0);
        $entity->method("getLayoutUpdateXml")->willReturn("");
        $entity->method("getCustomTheme")->willReturn("");
        $entity->method("getCustomRootTemplate")->willReturn("");
        $entity->method("getCustomLayoutUpdateXml")->willReturn("");
        $entity->method("getCustomThemeFrom")->willReturn("");
        $entity->method("getCustomThemeTo")->willReturn("");

        $this->pageRepository
            ->expects($this->once())
            ->method("getById")
            ->with($id)
            ->willReturn($entity);

        $fp = fopen("php://memory", "r+");
        $result = $this->exporter->exportEntityCsv($type, $id, $fp, $headers);

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        $lines = explode("\n", trim($csv));
        $this->assertSame(implode(",", $headers), $lines[0]);
        $this->assertStringContainsString("42", $lines[1]);
        $this->assertSame($entity, $result);
    }
}
