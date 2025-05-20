<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Processor;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\Page;
use PHPUnit\Framework\TestCase;
use Ronangr1\CmsImportExport\Processor\Entity;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ronangr1\CmsImportExport\Service\Config;
use Magento\Store\Model\Store;

class EntityTest extends TestCase
{
    private $pageRepository;
    private $blockRepository;
    private $blockByIdentifier;
    private $pageByIdentifier;
    private $blockFactory;
    private $pageFactory;
    private $config;
    private $entity;

    protected function setUp(): void
    {
        $this->pageRepository = $this->createMock(PageRepositoryInterface::class);
        $this->blockRepository = $this->createMock(BlockRepositoryInterface::class);
        $this->blockByIdentifier = $this->createMock(GetBlockByIdentifierInterface::class);
        $this->pageByIdentifier = $this->createMock(GetPageByIdentifierInterface::class);
        $this->blockFactory = $this->createMock(BlockFactory::class);
        $this->pageFactory = $this->createMock(PageFactory::class);
        $this->config = $this->createMock(Config::class);

        $this->entity = new Entity(
            $this->pageRepository,
            $this->blockRepository,
            $this->blockByIdentifier,
            $this->pageByIdentifier,
            $this->blockFactory,
            $this->pageFactory,
            $this->config
        );
    }

    public function testSaveCmsPage()
    {
        $page = $this->createMock(Page::class);

        $this->pageRepository->expects($this->once())
            ->method("save")
            ->with($page);

        $this->entity->save("cms_page", $page);
    }

    public function testSaveCmsBlock()
    {
        $page = $this->createMock(Block::class);

        $this->blockRepository->expects($this->once())
            ->method("save")
            ->with($page);

        $this->entity->save("cms_block", $page);
    }

    public function testSaveThrowsOnUnknownType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->entity->save("cms_unknown", (object)[]);
    }

    public function testSaveThrowsOnLocalizedException()
    {
        $page = $this->createMock(Page::class);
        $this->pageRepository
            ->method("save")
            ->willThrowException(new LocalizedException(__("Error")));
        $this->expectException(\RuntimeException::class);
        $this->entity->save("cms_page", $page);
    }

    public function testBuildEntityFromRowCmsPageWithOverwrite()
    {
        $row = [
            "id" => 42,
            "title" => "Dummy",
            "identifier" => "dummy",
            "is_active" => 1,
            "content_heading" => "heading",
            "meta_keywords" => "dummy, dummo",
            "meta_description" => "dummy description",
        ];

        $pageMock = $this->getMockBuilder(Page::class)
            ->onlyMethods(["getId","setTitle","setIdentifier","setIsActive","setContentHeading","setMetaKeywords","setMetaDescription","setContent"])
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock->expects($this->any())->method("getId")->willReturn($row["id"]);
        $pageMock->expects($this->once())->method("setTitle")->with($row["title"]);
        $pageMock->expects($this->once())->method("setIdentifier")->with($row["identifier"]);
        $pageMock->expects($this->once())->method("setIsActive")->with($row["is_active"]);
        $pageMock->expects($this->once())->method("setContentHeading")->with($row["content_heading"]);
        $pageMock->expects($this->once())->method("setMetaKeywords")->with($row["meta_keywords"]);
        $pageMock->expects($this->once())->method("setMetaDescription")->with($row["meta_description"]);

        $this->pageByIdentifier->expects($this->once())
            ->method("execute")
            ->with("dummy", Store::DEFAULT_STORE_ID)
            ->willReturn($pageMock);
        $this->config->method("allowOverwrite")->willReturn(true);
        $this->config->method("allowDownloadMedia")->willReturn(false);

        $result = $this->entity->buildEntityFromRow("cms_page", $row);
        $this->assertSame($pageMock, $result);
    }

    public function testBuildEntityFromRowCmsPageWithExistNoOverwrite()
    {
        $row = [
            "title" => "Dummy", "identifier" => "dummy"
        ];

        $pageMock = $this->getMockBuilder(Page::class)
            ->onlyMethods(["getId"])
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock->method("getId")->willReturn(100);

        $this->pageByIdentifier
            ->method("execute")->willReturn($pageMock);
        $this->config->method("allowOverwrite")->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->entity->buildEntityFromRow("cms_page", $row);
    }

    public function testBuildEntityFromRowCmsPageThrowsOnIdentifierMissing()
    {
        $this->expectException(LocalizedException::class);
        $this->entity->buildEntityFromRow("cms_page", []);
    }

    public function testBuildEntityFromRowCmsPageNoSuchEntity()
    {
        $row = [
            "title" => "Dummy", "identifier" => "dummy"
        ];
        $pageMock = $this->getMockBuilder(Page::class)
            ->onlyMethods(["setTitle","setIdentifier","setIsActive","setContentHeading","setMetaKeywords","setMetaDescription","setContent"])
            ->disableOriginalConstructor()
            ->getMock();
        $pageMock->expects($this->once())->method("setTitle")->with("Dummy");
        $pageMock->expects($this->once())->method("setIdentifier")->with("dummy");

        $this->pageByIdentifier->method("execute")->willThrowException(new NoSuchEntityException());
        $this->pageFactory->expects($this->once())->method("create")->willReturn($pageMock);
        $this->config->method("allowOverwrite")->willReturn(true);
        $this->config->method("allowDownloadMedia")->willReturn(false);

        $this->entity->buildEntityFromRow("cms_page", $row);
    }

    public function testBuildEntityFromRowThrowsOnUnknownType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->entity->buildEntityFromRow("cms_unknown", []);
    }
}
