<?php
/**
 * File containing the LocationServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;

/**
 * Test case for operations in the LocationService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LocationService
 */
class LocationServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */;
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $user = $this->createUserVersion1();

        // ContentInfo for "Editors" user group
        $contentInfo = $contentService->loadContentInfo( $editorsGroupId );

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        $locationCreate = $locationService->newLocationCreateStruct( 5 );
        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = 'sindelfingen';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // This call will fail with an "UnauthorizedException"
        $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $user = $this->createUserVersion1();

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->loadLocation( $editorsGroupId );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadLocationByRemoteIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // remoteId of the "Editors" location in an eZ Publish demo installation
        $editorsRemoteId = 'f7dda2854fc68f7c8455d9cb14bd04a9';

        $locationService = $repository->getLocationService();

        $user = $this->createUserVersion1();

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->loadLocationByRemoteId( $editorsRemoteId );
        /* END: Use Case */
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */;
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation( $editorsGroupId );

        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->priority  = 3;
        $locationUpdateStruct->remoteId  = 'c7adcbf1e96bc29bca28c2d809d0c7ef69272651';
        $locationUpdateStruct->sortField = Location::SORT_FIELD_PRIORITY;
        $locationUpdateStruct->sortOrder = Location::SORT_ORDER_DESC;

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->updateLocation(
            $originalLocation,
            $locationUpdateStruct
        );
        /* END: Use Case */;
    }

    /**
     * Test for the loadMainLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadMainLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadMainLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */;
        $user = $this->createUserVersion1();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo( $editorsGroupId );

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->loadMainLocation( $contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the swapLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::swapLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSwapLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::swapLocation() is not implemented." );
    }

    /**
     * Test for the hideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::hideLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testHideLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation( $editorsGroupId );

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->hideLocation( $visibleLocation );
        /* END: Use Case */
    }

    /**
     * Test for the unhideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::unhideLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnhideLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation( $editorsGroupId );

        // Hide location
        $hiddenLocation = $locationService->hideLocation( $visibleLocation );

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->unhideLocation( $hiddenLocation );
        /* END: Use Case */
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $editorsGroupId );

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->deleteLocation( $location );
        /* END: Use Case */
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopySubtreeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // ID of the "Community" page location in an eZ Publish demo installation
        $communityLocationId = 167;

        // ID of the "Support" page location in an eZ Publish demo installation
        $supportLocationId = 96;

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation( $communityLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $supportLocationId );

        // Set media editor as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testMoveSubtreeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // ID of the "Community" page location in an eZ Publish demo installation
        $communityLocationId = 167;

        // ID of the "Support" page location in an eZ Publish demo installation
        $supportLocationId = 96;

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation( $communityLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $supportLocationId );

        // Set media editor as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );
        /* END: Use Case */
    }
}
