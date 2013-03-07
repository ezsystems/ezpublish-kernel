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
    protected $searchService;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' );
        $this->searchService = $this->getMock( 'eZ\\Publish\\API\\Repository\\SearchService' );
        $this->repository
            ->expects( $this->any() )
            ->method( 'getSearchService' )
            ->will( $this->returnValue( $this->searchService ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Tests\FieldType\Page\PageService::getValidBlockItemsAsContent
     */
    public function testGetValidBlockItemsAsContent()
    {
        $this->pageService->setStorageGateway( $this->storageGateway );
        $this->pageService->setRepository( $this->repository );
        $block = $this->buildBlock();
        $items = array(
            new Item( array( 'contentId' => 1 ) ),
            new Item( array( 'contentId' => 60 ) )
        );
        $content1 = new Content( array( 'internalFields' => array() ) );
        $content2 = clone $content1;
        $expectedResult = array( $content1, $content2 );

        $this->storageGateway
            ->expects( $this->once() )
            ->method( 'getValidBlockItems' )
            ->with( $block )
            ->will( $this->returnValue( $items ) );

        $searchResult = new SearchResult(
            array(
                'searchHits' => array(
                    new SearchHit( array( 'valueObject' => $content1 ) ),
                    new SearchHit( array( 'valueObject' => $content2 ) ),
                )
            )
        );
        $this->searchService
            ->expects( $this->once() )
            ->method( 'findContent' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Query' ) )
            ->will( $this->returnValue( $searchResult ) );

        // Calling assertion twice to test cache (comes along with search service/gateway methods that should be called only once. See above)
        $this->assertSame( $expectedResult, $this->pageService->getValidBlockItemsAsContent( $block ) );
        $this->assertSame( $expectedResult, $this->pageService->getValidBlockItemsAsContent( $block ) );
    }
}
