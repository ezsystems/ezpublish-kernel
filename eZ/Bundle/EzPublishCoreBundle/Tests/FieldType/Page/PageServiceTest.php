<?php

/**
 * File containing the PageServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\FieldType\Page;

use eZ\Publish\Core\FieldType\Tests\Page\PageServiceTest as BaseTest;
use eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class PageServiceTest extends BaseTest
{
    /**
     * Class to instantiate to get the page service.
     */
    const PAGESERVICE_CLASS = PageService::class;

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService::getValidBlockItemsAsContentInfo
     */
    public function testGetValidBlockItemsAsContentInfo()
    {
        $this->pageService->setStorageGateway($this->storageGateway);
        $block = $this->buildBlock();
        $items = [
            new Item(['contentId' => 1]),
            new Item(['contentId' => 60]),
        ];
        $content1 = new ContentInfo(['id' => 1]);
        $content2 = new ContentInfo(['id' => 60]);
        $expectedResult = [$content1, $content2];

        $this->storageGateway
            ->expects($this->once())
            ->method('getValidBlockItems')
            ->with($block)
            ->will($this->returnValue($items));

        $this->contentService
            ->expects($this->exactly(count($items)))
            ->method('loadContentInfo')
            ->with($this->logicalOr(1, 60))
            ->will($this->onConsecutiveCalls($content1, $content2));

        $this->assertSame($expectedResult, $this->pageService->getValidBlockItemsAsContentInfo($block));
    }
}
