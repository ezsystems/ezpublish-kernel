<?php
/**
 * File containing the LocationServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Test case for operations in the LocationService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LocationService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
 * @group integration
 * @group authorization
 */
class LocationServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $user = $this->createUserVersion1();

        // ContentInfo for "Editors" user group
        $contentInfo = $contentService->loadContentInfo( $editorsGroupId );

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        $locationCreate = $locationService->newLocationCreateStruct( 1 );
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );

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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationByRemoteId
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUpdateLocation
     */
    public function testUpdateLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation( $editorsGroupId );

        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->priority = 3;
        $locationUpdateStruct->remoteId = 'c7adcbf1e96bc29bca28c2d809d0c7ef69272651';
        $locationUpdateStruct->sortField = Location::SORT_FIELD_PRIORITY;
        $locationUpdateStruct->sortOrder = Location::SORT_ORDER_DESC;

        // Set current user to newly created user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->updateLocation(
            $originalLocation,
            $locationUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the swapLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::swapLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testSwapLocation
     */
    public function testSwapLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" Location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" Location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        $mediaLocation = $locationService->loadLocation( $mediaLocationId );
        $demoDesignLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Swaps the content referred to by the locations
        $locationService->swapLocation( $mediaLocation, $demoDesignLocation );

        $user = $this->createMediaUserVersion1();

        // Set media editor as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $locationService->swapLocation( $mediaLocation, $demoDesignLocation );
        /* END: Use Case */
    }

    /**
     * Test for the hideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::hideLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testHideLocation
     */
    public function testHideLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );

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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUnhideLocation
     */
    public function testUnhideLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );

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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testDeleteLocation
     */
    public function testDeleteLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );

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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // $mediaLocationId is the ID of the "Media" Location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" Location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

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
