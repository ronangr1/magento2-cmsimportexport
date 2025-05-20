<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Controller\Adminhtml\Ajax;

use PHPUnit\Framework\TestCase;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Ronangr1\CmsImportExport\Api\ImporterInterface;
use Ronangr1\CmsImportExport\Controller\Adminhtml\Ajax\Import;

class ImportTest extends TestCase
{
    private $contextMock;
    private $importerMock;
    private $resultFactoryMock;
    private $requestMock;
    private $jsonResultMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->importerMock = $this->createMock(ImporterInterface::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->jsonResultMock = $this->createMock(Json::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);

        $this->resultFactoryMock->method("create")
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->jsonResultMock);

        $this->contextMock->method("getResultFactory")
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->method("getRequest")
            ->willReturn($this->requestMock);
    }

    public function testExecuteWithNoParams()
    {
        $this->requestMock->method("getParams")->willReturn([]);

        $this->jsonResultMock->expects($this->once())
            ->method("setData")
            ->with([
                "success" => false,
                "message" => "Import failed"
            ]);

        $controller = new Import(
            $this->contextMock,
            $this->importerMock
        );

        $result = $controller->execute();
        $this->assertSame($this->jsonResultMock, $result);
    }

    public function testExecuteImportSuccess()
    {
        $params = ["entity_type" => "cms_page"];
        $fileArray = [
            "name" => "cms_page_11_export.zip",
            "full_path" => "cms_page_11_export.zip",
            "type" => "application/zip",
            "tmp_name" => "/tmp/phptBElPq",
            "error" => 0,
            "size" => 812
        ];

        $this->requestMock->method("getParams")->willReturn($params);
        $this->requestMock->method("getFiles")->with("import_file")->willReturn($fileArray);
        $this->requestMock->method("getParam")->with("entity_type")->willReturn("cms_page");

        $this->importerMock->expects($this->once())
            ->method("import")
            ->with($fileArray, "cms_page");

        $this->jsonResultMock->expects($this->once())
            ->method("setData")
            ->with([
                "success" => true,
                "message" => "Import completed"
            ]);

        $controller = new Import(
            $this->contextMock,
            $this->importerMock
        );

        $result = $controller->execute();
        $this->assertSame($this->jsonResultMock, $result);
    }

    public function testExecuteImportThrowsException()
    {
        $params = ["entity_type" => "cms_page"];
        $fileArray = [
            "name" => "cms_page_11_export.zip",
            "full_path" => "cms_page_11_export.zip",
            "type" => "application/zip",
            "tmp_name" => "/tmp/phptBElPq",
            "error" => 0,
            "size" => 812
        ];

        $this->requestMock->method("getParams")->willReturn($params);
        $this->requestMock->method("getFiles")->with("import_file")->willReturn($fileArray);
        $this->requestMock->method("getParam")->with("entity_type")->willReturn("cms_page");

        $this->importerMock->method("import")
            ->will($this->throwException(new \Exception("dummy error")));

        $this->jsonResultMock->expects($this->once())
            ->method("setData")
            ->with([
                "success" => false,
                "message" => "Import failed: dummy error"
            ]);

        $controller = new Import(
            $this->contextMock,
            $this->importerMock
        );

        $result = $controller->execute();
        $this->assertSame($this->jsonResultMock, $result);
    }
}
