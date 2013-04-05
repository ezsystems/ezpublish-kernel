<?php
/**
 * File containing the PageServiceTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\FieldType\Page;

use eZ\Publish\Core\FieldType\Tests\Page\PageServiceTest as BaseTest;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;

class PageServiceTest extends BaseTest
{
    /**
     * Class to instantiate to get the page service.
     */
    const PAGESERVICE_CLASS = 'eZ\\Bundle\\EzPublishCoreBundle\\FieldType\\Page\\PageService';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentService;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' );
        $this->contentService = $this->getMock( 'eZ\\Publish\\API\\Repository\\ContentService' );
        $this->repository
            ->expects( $this->any() )
            ->method( 'getContentService' )
            ->will( $this->returnValue( $this->contentService ) );
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService::getValidBlockItemsAsContentInfo
     */
    public function testGetValidBlockItemsAsContentInfo()
    {
        $this->pageService->setStorageGateway( $this->storageGateway );
        $this->pageService->setRepository( $this->repository );
        $block = $this->buildBlock();
        $items = array(
            new Item( array( 'contentId' => 1 ) ),
            new Item( array( 'contentId' => 60 ) )
        );
        $content1 = new ContentInfo( array( 'id' => 1 ) );
        $content2 = new ContentInfo( array( 'id' => 60 ) );
        $expectedResult = array( $content1, $content2 );

        $this->storageGateway
            ->expects( $this->once() )
            ->method( 'getValidBlockItems' )
            ->with( $block )
            ->will( $this->returnValue( $items ) );

        $this->contentService
            ->expects( $this->exactly( count( $items ) ) )
            ->method( 'loadContentInfo' )
            ->with( $this->logicalOr( 1, 60 ) )
            ->will( $this->onConsecutiveCalls( $content1, $content2 ) );

        $this->assertSame( $expectedResult, $this->pageService->getValidBlockItemsAsContentInfo( $block ) );
    }
}
