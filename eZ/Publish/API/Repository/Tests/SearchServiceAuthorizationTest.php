<?php
/**
 * File containing the SearchServiceAuthorizationTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;

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

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $searchService = $repository->getSearchService();
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadUser( $anonymousUserId ) );

        // Should return Content with location id: 2 as the anonymous user should have access to standard section
        $searchResult = $searchService->findContent( new Query( array( 'filter' => new Criterion\LocationId( 2 ) ) ) );
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

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $searchService = $repository->getSearchService();
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadUser( $anonymousUserId ) );

        // This call will return an empty search result
        $searchResult = $searchService->findContent( new Query( array( 'filter' => new Criterion\LocationId( 5 ) ) ) );
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

        $anonymousUserId = $this->generateId( 'user', 10 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $searchService = $repository->getSearchService();
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadUser( $anonymousUserId ) );

        // This call will fail with a "NotFoundException" as user does not have access
        $searchService->findSingle(
            new Criterion\ContentId(
                array( 4 )
            )
        );
        /* END: Use Case */
    }
}
