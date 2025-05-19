<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Controller\Adminhtml;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Ronangr1\CmsImportExport\Api\ExporterInterface;
use Exception;
use Ronangr1\CmsImportExport\Controller\Adminhtml\Exporter;

class ExporterTest extends TestCase
{
    private MockObject $context;

    private MockObject $fileFactory;

    private MockObject $exporter;

    private MockObject $redirectFactory;

    private MockObject $pageRepository;

    private MockObject $blockRepository;

    private MockObject $request;

    private MockObject $redirectResult;

    private MockObject $url;

    private MockObject $page;

    private MockObject $messageManager;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->fileFactory = $this->createMock(FileFactory::class);
        $this->exporter = $this->createMock(ExporterInterface::class);
        $this->redirectFactory = $this->createMock(RedirectFactory::class);
        $this->pageRepository = $this->createMock(PageRepositoryInterface::class);
        $this->blockRepository = $this->createMock(BlockRepositoryInterface::class);
        $this->request = $this->createMock(Http::class);
        $this->redirectResult = $this->createMock(Redirect::class);
        $this->url = $this->createMock(UrlInterface::class);

        $this->context->method("getRequest")->willReturn($this->request);
        $this->redirectFactory->method("create")->willReturn($this->redirectResult);
        $this->redirectResult->method("setPath")->willReturn($this->redirectResult);
        $this->context->method("getUrl")->willReturn($this->url);

    }

    public function testExecuteGenerateZip()
    {
        $fakeId = 99;
        $fakeFileName = "page_99.zip";
        $fakeFileContent = ["value" => "dummy content"];
        $fakeMime = "application/zip";

        $this->request->method("getParam")->with("id")->willReturn($fakeId);

        $this->exporter->expects($this->once())
            ->method("export")
            ->with($fakeId, "cms_page")
            ->willReturn([$fakeFileName, $fakeFileContent, $fakeMime]);

        $this->fileFactory->expects($this->once())
            ->method("create")
            ->with(
                $fakeFileName,
                $fakeFileContent,
                "var",
                $fakeMime
            );

        $this->page = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->page->method("getTitle")->willReturn("Dummy Page Title");

        $this->pageRepository
            ->method("getById")
            ->willReturn($this->page);

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->method("getMessageManager")->willReturn($this->messageManager);

        $controller = new class(
            $this->context,
            $this->fileFactory,
            $this->exporter,
            $this->redirectFactory,
            $this->pageRepository,
            $this->blockRepository,
            $this->url,
        ) extends Exporter {
            protected string $type = "cms_page";
        };

        $controller->execute();
    }

    public function testExecuteExportZipException()
    {
        $fakeId = 88;

        $this->request->method("getParam")->with("id")->willReturn($fakeId);

        $this->exporter->expects($this->once())
            ->method("export")
            ->with($fakeId, "cms_page")
            ->willThrowException(new Exception("BAD ZIP"));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("BAD ZIP");

        $controller = new class(
            $this->context,
            $this->fileFactory,
            $this->exporter,
            $this->redirectFactory,
            $this->pageRepository,
            $this->blockRepository,
            $this->url,
        ) extends Exporter {
            protected string $type = "cms_page";
        };

        $result = $controller->execute();
        $this->assertSame($this->redirectResult, $result);
    }
}
