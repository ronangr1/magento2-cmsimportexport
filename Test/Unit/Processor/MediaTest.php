<?php
/**
 * Copyright © Ronangr1, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Ronangr1\CmsImportExport\Test\Unit\Processor;

use Magento\Cms\Model\Page;
use Magento\Framework\Filter\Template;
use PHPUnit\Framework\TestCase;
use Ronangr1\CmsImportExport\Processor\Media;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Filesystem\Io\File;

class MediaTest extends TestCase
{
    private $directoryList;
    private $filterProvider;
    private $pageFilter;
    private $ioFile;
    private $media;

    protected function setUp(): void
    {
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->filterProvider = $this->createMock(FilterProvider::class);
        $this->ioFile = $this->createMock(File::class);
        $this->pageFilter = $this->getMockBuilder(Template::class)
            ->onlyMethods(["filter"])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterProvider->method("getPageFilter")->willReturn($this->pageFilter);

        $this->media = new Media(
            $this->directoryList,
            $this->filterProvider,
            $this->ioFile
        );
    }

    public function testImportMediaFromDirWithNoMediaDirectory()
    {
        $importDir = "/not/exist";
        $content = "no media";
        $output = $this->media->importMediaFromDir($importDir, $content);
        $this->assertSame($content, $output);
    }

    public function testExportEntityMediaCopiesMediaFoundInImgTags()
    {
        $entity = $this->getMockBuilder(Page::class)
            ->onlyMethods(["getContent"])
            ->disableOriginalConstructor()
            ->getMock();

        $entity->method("getContent")->willReturn("<img src='/media/foo.png'><img src='ignore.jpg'>");
        $this->pageFilter->method("filter")->willReturn("<img src='/media/foo.png'><img src='ignore.jpg'>");

        $this->directoryList->method("getPath")->with(DirectoryList::MEDIA)->willReturn("/pub/media");

        $this->ioFile->expects($this->once())
            ->method("mkdir")->with("/export/media", 0755);

        $this->ioFile->expects($this->once())
            ->method("cp")->with("/pub/media/foo.png", "/export/media/foo.png");

        $this->media->exportEntityMedia($entity, "/export");
    }

    public function testExportEntityMediaWhenNoMediaInContent()
    {
        $entity = $this->getMockBuilder(Page::class)
            ->onlyMethods(["getContent"])
            ->disableOriginalConstructor()
            ->getMock();

        $entity->method("getContent")->willReturn("No image");
        $this->pageFilter->method("filter")->willReturn("No image");

        $this->ioFile->expects($this->never())->method("mkdir");
        $this->ioFile->expects($this->never())->method("cp");

        $this->media->exportEntityMedia($entity, "/export");
    }
}
