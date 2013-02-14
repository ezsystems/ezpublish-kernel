<?php
/**
 * File containing the TrashServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the TrashService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\TrashService
 * @group integration
 * @group authorization
 */
class TrashServiceAuthorizationTest extends BaseTrashServiceTest
{
    /**
     * Test for the loadTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testLoadTrashItem
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
     */
    public function testLoadTrashItemThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->loadTrashItem( $trashItem->id );
        /* END: Use Case */
    }

    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
     */
    public function testTrashThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // remoteId of the "Media" page main location
        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        $userService = $repository->getUserService();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        // Load "Media" page location
        $mediaLocation = $locationService->loadLocationByRemoteId(
            $mediaRemoteId
        );

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->trash( $mediaLocation );
        /* END: Inline */
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
     */
    public function testRecoverThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->recover( $trashItem );
        /* END: Use Case */
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
     */
    public function testRecoverThrowsUnauthorizedExceptionWithNewParentLocationParameter()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $homeLocationId = $this->generateId( 'location', 2 );
        /* BEGIN: Use Case */
        // $homeLocationId is the ID of the "Home" location in an eZ Publish
        // demo installation

        $trashItem = $this->createTrashItem();

        // Get the new parent location
        $newParentLocation = $locationService->loadLocation( $homeLocationId );

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->recover( $trashItem, $newParentLocation );
        /* END: Use Case */
    }

    /**
     * Test for the emptyTrash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::emptyTrash()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testEmptyTrash
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
     */
    public function testEmptyTrashThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->emptyTrash();
        /* END: Use Case */
    }

    /**
     * Test for the deleteTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::deleteTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testDeleteTrashItem
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
     */
    public function testDeleteTrashItemThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->deleteTrashItem( $trashItem );
        /* END: Use Case */
    }
}
