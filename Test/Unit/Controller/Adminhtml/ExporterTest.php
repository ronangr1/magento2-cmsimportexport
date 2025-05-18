<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Controller\Adminhtml;

use PHPUnit\Framework\TestCase;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ronangr1\CmsImportExport\Api\ExporterInterface;
use Ronangr1\CmsImportExport\Controller\Adminhtml\Exporter;

class ExporterTest extends TestCase
{
    private $contextMock;
    private $fileFactoryMock;
    private $exporterMock;
    private $redirectFactoryMock;
    private $requestMock;
    private $messageManagerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->fileFactoryMock = $this->createMock(FileFactory::class);
        $this->exporterMock = $this->createMock(ExporterInterface::class);
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->contextMock->method("getRequest")->willReturn($this->requestMock);
        $this->contextMock->method("getMessageManager")->willReturn($this->messageManagerMock);
    }

    private function getExporterInstance()
    {
        return new class(
            $this->contextMock,
            $this->fileFactoryMock,
            $this->exporterMock,
            $this->redirectFactoryMock
        ) extends Exporter {};
    }

    public function testExecuteWithNoId()
    {
        $this->requestMock->method("getParam")->with("id")->willReturn(null);
        $this->messageManagerMock->expects($this->once())
            ->method("addErrorMessage")
            ->with(__("You must save the entity before exporting."));

        $redirectMock = $this->createMock(Redirect::class);
        $redirectMock->expects($this->once())
            ->method("setPath")
            ->with("cms/*/new")
            ->willReturnSelf();

        $this->redirectFactoryMock->expects($this->once())
            ->method("create")
            ->willReturn($redirectMock);

        $controller = $this->getExporterInstance();
        $result = $controller->execute();
        $this->assertSame($redirectMock, $result);
    }

    public function testExecuteWithValidId()
    {
        $this->requestMock->method("getParam")->with("id")->willReturn(5);

        $fileName = "export.zip";
        $content = "data";
        $mime = "application/zip";

        $this->exporterMock->expects($this->once())
            ->method("export")
            ->with(5, "cms_default")
            ->willReturn([$fileName, $content, $mime]);

        $fileResultMock = new \stdClass();

        $this->fileFactoryMock->expects($this->once())
            ->method("create")
            ->with($fileName, $content, DirectoryList::VAR_DIR, $mime)
            ->willReturn($fileResultMock);

        $controller = $this->getExporterInstance();
        $result = $controller->execute();
        $this->assertSame($fileResultMock, $result);
    }
}
