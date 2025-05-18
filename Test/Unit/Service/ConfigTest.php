<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Service;

use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ronangr1\CmsImportExport\Service\Config;

class ConfigTest extends TestCase
{
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
    }

    public function testAllowDownloadMediaReturnsTrue()
    {
        $this->scopeConfigMock->method("isSetFlag")
            ->with("cmsimportexport/media/allow_download", ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn(true);

        $helper = new Config($this->scopeConfigMock);
        $this->assertTrue($helper->allowDownloadMedia());
    }

    public function testAllowDownloadMediaReturnsFalse()
    {
        $this->scopeConfigMock->method("isSetFlag")
            ->with("cmsimportexport/media/allow_download", ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn(false);

        $helper = new Config($this->scopeConfigMock);
        $this->assertFalse($helper->allowDownloadMedia());
    }

    public function testAllowOverwriteReturnsTrue()
    {
        $this->scopeConfigMock->method("isSetFlag")
            ->with("cmsimportexport/general/allow_overwrite", ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn(true);

        $helper = new Config($this->scopeConfigMock);
        $this->assertTrue($helper->allowOverwrite());
    }

    public function testAllowOverwriteReturnsFalse()
    {
        $this->scopeConfigMock->method("isSetFlag")
            ->with("cmsimportexport/general/allow_overwrite", ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->willReturn(false);

        $helper = new Config($this->scopeConfigMock);
        $this->assertFalse($helper->allowOverwrite());
    }
}
