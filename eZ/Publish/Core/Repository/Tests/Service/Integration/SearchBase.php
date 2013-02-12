<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\SearchBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;

/**
 * Test case for Content service
 */
abstract class SearchBase extends BaseServiceTest
{
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
}
