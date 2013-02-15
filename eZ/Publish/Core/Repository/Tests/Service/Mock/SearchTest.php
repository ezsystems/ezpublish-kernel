<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\SearchBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;

/**
 * Mock test case for Content service
 */
class SearchTest extends BaseServiceMockTest
{
    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentBehaviour()
    {
        $searchService = $this->getRepository()->getSearchService();

        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
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
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     */
    public function testFindSingle()
    {
        // Re-enable when contentType has been removed from ContentInfo
        $this->markTestIncomplete( "Test not implemented:" . __METHOD__ );
        $searchService = $this->getRepository()->getSearchService();

        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $searchHandlerMock->expects(
            $this->once()
        )->method(
            "findSingle"
        )->with(
            $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" ),
            $this->isType( "array" )
        )->will(
            $this->returnValue(
                new SPIContent(
                    array(
                        'versionInfo' => new SPIVersionInfo(
                            array(
                                'contentInfo' => new SPIContentInfo( array( 'contentTypeId' => 1 ) )
                            )
                        ),
                        'fields' => array(),
                        'relations' => array()
                    )
                )
            )
        );

        /*$typeHandlerMock = $this->getPersistenceMockHandler( 'Content\\Type\\Handler' );
        $typeHandlerMock->expects(
            $this->once()
        )->method(
            "load"
        )->with(
            $this->equalTo( 1 ),
            $this->equalTo( 0 )
        )->will(
            $this->returnValue( new SPIContentType( array(
                'id' => 1
            ) ) )
        );*/

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
    public function testFindSingleThrowsNotFoundExceptionDueToPermissions()
    {
        $this->markTestIncomplete( "Test not implemented:" . __METHOD__ );
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedSearchService( array $methods = null )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\SearchService",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()->searchHandler()
            )
        );
    }
}
