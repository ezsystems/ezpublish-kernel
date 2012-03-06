<?php
/**
 * File containing the TrashServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the TrashService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\TrashService
 * @d epends \eZ\Publish\API\Repository\Tests\TrashServiceTest
 * @d epends \eZ\Publish\API\Repository\Tests\UserServiceTest
 */
class TrashServiceAuthorizationTest extends BaseTrashServiceTest
{
    /**
     * Test for the loadTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadTrashItemThrowsUnauthorizedException()
    {
        $repository   = $this->getRepository();
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
     */
    public function testTrashThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // remoteId of the "Community" page main location
        $communityRemoteId = 'c4604fb2e100a6681a4f53fbe6e5eeae';

        $userService     = $repository->getUserService();
        $trashService    = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        // Load "Community" page location
        $communityLocation = $locationService->loadLocationByRemoteId(
            $communityRemoteId
        );

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->trash( $communityLocation );
        /* END: Inline */
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRecoverThrowsUnauthorizedException()
    {
        $repository   = $this->getRepository();
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
     */
    public function testRecoverThrowsUnauthorizedExceptionWithLocationCreateStructParameter()
    {
        $repository      = $this->getRepository();
        $trashService    = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        // ID of the "Home" location in an eZ Publish demo installation
        $homeLocationId = 2;

        $trashItem = $this->createTrashItem();

        // Get a location create without property changes.
        $locationCreate = $locationService->newLocationCreateStruct( $homeLocationId );

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an "UnauthorizedException"
        $trashService->recover( $trashItem, $locationCreate );
        /* END: Use Case */
    }

    /**
     * Test for the emptyTrash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::emptyTrash()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testEmptyTrashThrowsUnauthorizedException()
    {
        $repository   = $this->getRepository();
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
     */
    public function testDeleteTrashItemThrowsUnauthorizedException()
    {
        $repository   = $this->getRepository();
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
