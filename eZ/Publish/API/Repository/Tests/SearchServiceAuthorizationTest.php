<?php
/**
 * File containing the SearchServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Handler;

/**
 * Test case for operations in the SearchService.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
 * @group integration
 * @group authorization
 */
class SearchServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the findContent() method but with anonymous user.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testFindContent
     */
    public function testFindContent()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $searchService = $repository->getSearchService();
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // Should return Content with location id: 2 as the anonymous user should have access to standard section
        $searchResult = $searchService->findContent( new Query( array( 'criterion' => new Criterion\LocationId( 2 ) ) ) );
        /* END: Use Case */

        self::assertEquals( 1, $searchResult->totalCount, "Search query should return totalCount of 1" );
        self::assertNotEmpty( $searchResult->searchHits, "\$searchResult->searchHits should not be empty" );
        self::assertEquals( "Home", $searchResult->searchHits[0]->valueObject->contentInfo->name );
    }

    /**
     * Test for the findContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testFindContent
     */
    public function testFindContentEmptyResult()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $searchService = $repository->getSearchService();
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will return an empty search result
        $searchResult = $searchService->findContent( new Query( array( 'criterion' => new Criterion\LocationId( 5 ) ) ) );
        /* END: Use Case */

        self::assertEmpty(
            $searchResult->searchHits,
            "Expected Not Found exception, got content with name: " .
            (!empty($searchResult->searchHits) ? $searchResult->searchHits[0]->valueObject->contentInfo->name : '')
        );
        self::assertEquals( 0, $searchResult->totalCount, "Search query should return totalCount of 0" );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SearchService::findSingle()
     * @expectedException eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\SearchServiceTest::testFindSingle
     */
    public function testFindSingleThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $searchService = $repository->getSearchService();
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "NotFoundException" as user does not have access
        $searchService->findSingle(
            new Criterion\ContentId(
                array( 4 )
            )
        );
        /* END: Use Case */
    }
}
