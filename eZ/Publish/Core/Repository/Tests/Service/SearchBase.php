<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\SearchBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\Core\Repository\Values\Content\Content,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Search\SearchHit,
    eZ\Publish\API\Repository\Values\Content\Search\SearchResult,
    eZ\Publish\API\Repository\Exceptions\NotFoundException,

    eZ\Publish\SPI\Persistence\Content as SPIContent,
    eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo,
    eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;

/**
 * Test case for Content service
 */
abstract class SearchBase extends BaseServiceTest
{
    /**
     * Search handler mock
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Search\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchHandlerMock;

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentBehaviour()
    {
        $searchService = $this->repository->getSearchService();

        $refObject = new \ReflectionObject( $searchService );
        $refProperty = $refObject->getProperty( 'searchHandler' );
        $refProperty->setAccessible( true );
        $refProperty->setValue(
            $searchService,
            $this->getSearchHandlerMock()
        );

        $searchHandlerMock = $this->getSearchHandlerMock();
        $searchHandlerMock->expects(
            $this->once()
        )->method(
            "findContent"
        )->with(
            $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query" ),
            $this->isType( "array" )
        )->will(
            $this->returnValue( new SearchResult( array( 'totalCount' => 0 ) ) )
        );

        $searchService->findContent(
            new Query(
                array(
                    "criterion" => new Criterion\ContentId( 4 ),
                    "offset" => 0,
                    "limit" => 10,
                    "sortClauses" => array()
                )
            ),
            array( "languages" => array( "eng-GB" ) ),
            false
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContent()
    {
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();
        $query = new Query(
            array(
                "criterion" => new Criterion\ContentId( array( 4 ) ),
                "offset" => 0
            )
        );

        $searchResult = $searchService->findContent(
            $query,
            array(),
            false
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchResult",
            $searchResult
        );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertCount( $searchResult->totalCount, $searchResult->searchHits );
        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchHit",
            reset( $searchResult->searchHits )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithLanguageFilter()
    {
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();
        $query = new Query(
            array(
                "criterion" => new Criterion\ContentId( array( 4 ) ),
                "offset" => 0
            )
        );

        $searchResult = $searchService->findContent(
            $query,
            array( "languages" => array( "eng-US" ) ),
            false
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchResult",
            $searchResult
        );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertCount( $searchResult->totalCount, $searchResult->searchHits );
        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchHit",
            reset( $searchResult->searchHits )
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindContent
     * @depends testFindContentWithLanguageFilter
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     */
    public function testFindSingle()
    {
        $searchService = $this->repository->getSearchService();

        $refObject = new \ReflectionObject( $searchService );
        $refProperty = $refObject->getProperty( 'searchHandler' );
        $refProperty->setAccessible( true );
        $refProperty->setValue(
            $searchService,
            $this->getSearchHandlerMock()
        );

        $searchHandlerMock = $this->getSearchHandlerMock();
        $searchHandlerMock->expects(
            $this->once()
        )->method(
            "findSingle"
        )->with(
            $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" ),
            $this->isType( "array" )
        )->will(
            $this->returnValue( new SPIContent( array(
                'versionInfo' => new SPIVersionInfo(
                    array(
                        'contentInfo' => new SPIContentInfo( array( 'contentTypeId' => 1 ) )
                    )
                ),
                'fields' => array(),
                'relations' => array()
            ) ) )
        );

        /* BEGIN: Use Case */
        $content = $searchService->findSingle(
            new Criterion\ContentId( array( 42 ) ),
            array( "languages" => array( "eng-GB" ) ),
            false
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\Values\\Content\\Content",
            $content
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindSingle
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();

        // Throws an exception because content with given id does not exist
        $searchResult = $searchService->findSingle(
            new Criterion\ContentId( array( PHP_INT_MAX ) ),
            array( "languages" => array( "eng-GB" ) ),
            false
        );
        /* END: Use Case */
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindSingle
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleThrowsNotFoundExceptionDueToPermissions()
    {
        $this->markTestIncomplete( "Test not implemented:" . __METHOD__ );
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindSingle
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleThrowsInvalidArgumentException()
    {
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();

        // Throws an exception because more than one result was returned for the given query
        $searchResult = $searchService->findSingle(
            new Criterion\ContentId( array( 4, 10 ) ),
            array( "languages" => array( "eng-GB" ) )
        );
        /* END: Use Case */
    }

    /**
     * Returns a SearchHandler mock
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSearchHandlerMock()
    {
        if ( !isset( $this->searchHandlerMock ) )
        {
            $this->searchHandlerMock = $this->getMock(
                "eZ\\Publish\\SPI\\Persistence\\Content\\Search\\Handler",
                array(),
                array(),
                '',
                false
            );
        }
        return $this->searchHandlerMock;
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * @param string[] $methods
     * @return \eZ\Publish\Core\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedService( array $methods = array() )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\SearchService",
            $methods,
            array(
                $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' ),
                $this->getSearchHandlerMock()
            )
        );
    }
}
