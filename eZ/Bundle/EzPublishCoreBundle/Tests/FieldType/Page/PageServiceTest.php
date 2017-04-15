<?php

/**
 * File containing the PageServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\FieldType\Page;

use eZ\Publish\Core\FieldType\Tests\Page\PageServiceTest as BaseTest;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class PageServiceTest extends BaseTest
{
    /**
     * Class to instantiate to get the page service.
     */
    const PAGESERVICE_CLASS = 'eZ\\Bundle\\EzPublishCoreBundle\\FieldType\\Page\\PageService';

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService::getValidBlockItemsAsContentInfo
     */
    public function testGetValidBlockItemsAsContentInfo()
    {
        $this->pageService->setStorageGateway($this->storageGateway);
        $block = $this->buildBlock();
        $items = array(
            new Item(array('contentId' => 1)),
            new Item(array('contentId' => 60)),
        );
        $content1 = new ContentInfo(array('id' => 1));
        $content2 = new ContentInfo(array('id' => 60));
        $expectedResult = array($content1, $content2);

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

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService::getValidBlockItemsAsContent
     */
    public function testGetValidBlockItemsAsContent()
    {
        $this->pageService->setStorageGateway( $this->storageGateway );
        $this->pageService->setRepository( $this->repository );
        $block = $this->buildBlock();

        $contentId1 = 1;
        $contentId2 = 60;
        $items = array(
            new Item( array( 'contentId' => $contentId1 ) ),
            new Item( array( 'contentId' => $contentId2 ) )
        );

        $content1 = $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $content2 = $this->getMockForAbstractClass( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $expectedResult = array( $content1, $content2 );

        $this->storageGateway
            ->expects( $this->once() )
            ->method( 'getValidBlockItems' )
            ->with( $block )
            ->will( $this->returnValue( $items ) );

        $this->contentService
            ->expects( $this->exactly( count( $items ) ) )
            ->method( 'loadContent' )
            ->with( $this->logicalOr( $contentId1, $contentId2 ) )
            ->will( $this->onConsecutiveCalls( $content1, $content2 ) );

        $this->assertSame( $expectedResult, $this->pageService->getValidBlockItemsAsContent( $block ) );
    }
}
