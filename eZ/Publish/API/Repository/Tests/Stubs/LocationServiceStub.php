<?php
/**
 * File containing the LocationUpdateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\LocationService;

use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationList;

use eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;

/**
 * Location service, used for complex subtree operations
 *
 * @example Examples/location.php
 *
 * @package eZ\Publish\API\Repository
 */
class LocationServiceStub implements LocationService
{
    /**
     * Repository stub
     *
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    protected $repository;

    /**
     * @var int
     */
    protected $nextLocationId = 0;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\LocationStub[]
     */
    protected $locations = array();

    /**
     * Creates a new LocationServiceStub
     *
     * @param RepositoryStub $repository
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;
        $this->initFromFixture();
    }

    /**
     * Instantiates a new location create class
     *
     * @param int $parentLocationId the parent under which the new location should be created
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public function newLocationCreateStruct( $parentLocationId )
    {
        return new LocationCreateStruct(
            array(
                'parentLocationId' => $parentLocationId
            )
        );
    }

    /**
     * Creates the new $location in the content repository for the given content
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  if the content is already below the specified parent
     *                                        or the parent is a sub location of the location the content
     *                                        or if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created Location
     *
     */
    public function createLocation( ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct )
    {
        $parentLocation = $this->loadLocation( $locationCreateStruct->parentLocationId );

        if ( false === $this->repository->canUser( 'content', 'create', $parentLocation ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->checkContentNotInPath( $contentInfo, $parentLocation );
        $this->checkContentNotInTree( $contentInfo, $parentLocation );
        $this->checkRemoteIdNotTaken( $locationCreateStruct->remoteId );

        if ( null === $locationCreateStruct->remoteId )
        {
            $locationCreateStruct->remoteId = md5( uniqid( __METHOD__, true ) );
        }

        $data = array();
        foreach ( $locationCreateStruct as $propertyName => $propertyValue )
        {
            $data[$propertyName] = $propertyValue;
        }

        $data['contentInfo'] = $contentInfo;

        $data['id'] = ++$this->nextLocationId;
        $data['pathString'] = $parentLocation->pathString . $data['id'] . '/';
        $data['depth'] = substr_count( $data['pathString'], '/' ) - 2;
        $data['invisible'] = $locationCreateStruct->hidden;

        $location = new LocationStub( $data );
        $this->locations[$location->id] = $location;

        // Set main location if not set before.
        if ( null === $contentInfo->mainLocationId )
        {
            $contentInfo->setMainLocationId( $location->id );
        }

        return $location;
    }

    /**
     * Checks if the given $remoteId is already taken by another Location.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if the remoteId exists already.
     * @param string $remoteId
     *
     * @return void
     */
    protected function checkRemoteIdNotTaken( $remoteId )
    {
        foreach ( $this->locations as $location )
        {
            if ( $location->remoteId == $remoteId )
            {
                throw new Exceptions\InvalidArgumentExceptionStub;
            }
        }
    }

    /**
     * Checks that the given $contentInfo does not occur in the tree starting
     * at $location.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if the content is in the tree of $location.
     * @param ContentInfo $contentInfo
     * @param Location $location
     *
     * @return void
     */
    protected function checkContentNotInTree( ContentInfo $contentInfo, Location $location )
    {
        if ( $location->contentInfo == $contentInfo )
        {
            throw new Exceptions\InvalidArgumentExceptionStub;
        }
        foreach ( $this->loadLocationChildren( $location )->locations as $childLocation )
        {
            $this->checkContentNotInTree( $contentInfo, $childLocation );
        }
    }

    /**
     * Checks that the given $contentInfo does not occur in the tree starting
     * at $location.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if the content is in the tree of $location.
     * @param ContentInfo $contentInfo
     * @param Location $location
     *
     * @return void
     */
    protected function checkContentNotInPath( ContentInfo $contentInfo, Location $location )
    {
        if ( $location->contentInfo == $contentInfo )
        {
            throw new Exceptions\InvalidArgumentExceptionStub;
        }

        if ( $location->parentLocationId !== $location->id )
        {
            $this->checkContentNotInPath(
                $contentInfo,
                $this->loadLocation( $location->parentLocationId )
            );
        }
    }

    /**
     * Loads a location object from its $locationId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified location is not found
     *
     * @param int $locationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocation( $locationId )
    {
        if ( false === isset( $this->locations[$locationId] ) )
        {
            throw new Exceptions\NotFoundExceptionStub;
        }
        if ( false === $this->repository->canUser( 'content', 'read', $this->locations[$locationId] ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        return $this->locations[$locationId];
    }

    /**
     * Loads a location object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified location is not found
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocationByRemoteId( $remoteId )
    {
        foreach ( $this->locations as $location )
        {
            if ( $location->remoteId != $remoteId )
            {
                continue;
            }
            if ( false === $this->repository->canUser( 'content', 'create', $location ) )
            {
                throw new UnauthorizedExceptionStub( 'What error code should be used?' );
            }
            return $location;
        }
        throw new Exceptions\NotFoundExceptionStub;
    }

    /**
     * Instantiates a new location update class
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct
     */
    public function newLocationUpdateStruct()
    {
        return new LocationUpdateStruct();
    }

    /**
     * Updates $location in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to update this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException   if if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct $locationUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the updated Location
     */
    public function updateLocation( Location $location, LocationUpdateStruct $locationUpdateStruct )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $location ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->checkRemoteIdNotExist( $locationUpdateStruct );

        $data = $this->locationToArray( $location );

        foreach ( $locationUpdateStruct as $propertyName => $propertyValue )
        {
            $data[$propertyName] = $propertyValue;
        }

        $updatedLocation = new LocationStub( $data );
        $this->locations[$updatedLocation->id] = $updatedLocation;

        return $updatedLocation;
    }

    /**
     * Checks that the remote ID used in $locationUpdateStruct does not exist
     *
     * @param LocationUpdateStruct $locationUpdateStruct
     *
     * @return void
     */
    protected function checkRemoteIdNotExist( LocationUpdateStruct $locationUpdateStruct )
    {
        foreach ( $this->locations as $location )
        {
            if ( $location->remoteId == $locationUpdateStruct->remoteId )
            {
                throw new Exceptions\InvalidArgumentExceptionStub;
            }
        }
    }

    /**
     * Returns the data of the given $location as an array
     *
     * @param Location $location
     *
     * @return array
     */
    protected function locationToArray( Location $location )
    {
        return array(
            'id' => $location->id,
            'priority' => $location->priority,
            'hidden' => $location->hidden,
            'invisible' => $location->invisible,
            'remoteId' => $location->remoteId,
            'contentInfo' => $location->contentInfo,
            'parentLocationId' => $location->parentLocationId,
            'pathString' => $location->pathString,
            'depth' => $location->depth,
            'sortField' => $location->sortField,
            'sortOrder' => $location->sortOrder,
        );
    }

    /**
     * Loads the locations for the given content object.
     *
     * If a $rootLocation is given, only locations that belong to this location are returned.
     * The location list is also filtered by permissions on reading locations.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if there is no published version yet
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location $rootLocation
     *
     * @return array An array of {@link Location}
     */
    public function loadLocations( ContentInfo $contentInfo, Location $rootLocation = null )
    {
        if ( $contentInfo->published === false )
        {
            throw new Exceptions\BadStateExceptionStub;
        }

        $subPath = ( $rootLocation === null ? '/' : $rootLocation->pathString );

        $locations = array();
        foreach ( $this->locations as $candidateLocation )
        {
            if ( $candidateLocation->getContentInfo() === null )
            {
                // Skip root location
                continue;
            }
            if ( $contentInfo->id == $candidateLocation->getContentInfo()->id
                 && strpos( $candidateLocation->pathString, $subPath ) === 0
                )
            {
                $locations[] = $candidateLocation;
            }
        }
        return $locations;
    }

    /**
     * Loads children which are readable by the current user of a location object sorted by sortField and sortOrder
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationList
     */
    public function loadLocationChildren( Location $location, $offset = 0, $limit = -1 )
    {
        $children = $this->loadLocationChildrenById( $location->id );

        usort(
            $children,
            function ( $a, $b )
            {
                if ( $a->priority == $b->priority )
                {
                    // Sort by ID for same priorities
                    return ( $a->id < $b->id ) ? -1 : 1;
                }
                return ( $a->priority < $b->priority ) ? -1 : 1;
            }
        );

        return new LocationList(
            array(
                "locations" => array_slice( $children, $offset, ( $limit == -1 ? null : $limit ) ),
                "totalCount" => count( $children )
            )
        );
    }

    /**
     * Returns all location children based on their ID
     *
     * @param int $locationId
     *
     * @return array Of {@link Location}
     */
    private function loadLocationChildrenById( $locationId )
    {
        $children = array();
        foreach ( $this->locations as $potentialChild )
        {
            if ( $potentialChild->parentLocationId == $locationId )
            {
                $children[] = $potentialChild;
            }
        }

        return $children;
    }

    /**
     * Returns the number of children which are readable by the current user of a location object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return int
     */
    public function getLocationChildCount( Location $location )
    {
        return count( $this->loadLocationChildrenById( $location->id ) );
    }

    /**
     * Swaps the contents hold by the $location1 and $location2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to swap content
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location1
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location2
     */
    public function swapLocation( Location $location1,  Location $location2 )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $location1, $location2 ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        // Although the method is named swapLocation(), this method should
        // actually swap the content nodes. This is intentionally.
        $contentInfo1 = $location1->getContentInfo();
        $contentInfo2 = $location2->getContentInfo();

        $location1->setContentInfo( $contentInfo2 );
        $location2->setContentInfo( $contentInfo1 );
    }

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to hide this location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location $location, with updated hidden value
     */
    public function hideLocation( Location $location )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $location->contentInfo ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $location->hide();

        foreach ( $this->loadLocationChildren( $location )->locations as $child )
        {
            $this->markInvisible( $child );
        }

        return $location;
    }

    /**
     * Marks the sub-tree starting at $location invisible
     *
     * @param Location $location
     *
     * @return void
     */
    protected function markInvisible( Location $location )
    {
        $location->makeInvisible();

        foreach ( $this->loadLocationChildren( $location )->locations as $child )
        {
            $this->markInvisible( $child );
        }
    }

    /**
     * Unhides the $location.
     *
     * This method and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to unhide this location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location $location, with updated hidden value
     */
    public function unhideLocation( Location $location )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $location ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $location->unhide();

        foreach ( $this->loadLocationChildren( $location )->locations as $child )
        {
            $this->markVisible( $child );
        }

        return $location;
    }

    /**
     * Marks the subtree indicated by $location as visible.
     *
     * The process stops, when a hidden location is found in the subtree.
     *
     * @param mixed $location
     *
     * @return void
     */
    protected function markVisible( $location )
    {
        if ( $location->hidden == true )
        {
            // Stop as soon as a hidden location is found
            return;
        }
        $location->makeVisible();

        foreach ( $this->loadLocationChildren( $location )->locations as $child )
        {
            $this->markVisible( $child );
        }
    }

    /**
     * Deletes $location and all its descendants.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this location or a descendant
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function deleteLocation( Location $location )
    {
        if ( false === $this->repository->canUser( 'content', 'remove', $location ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->repository->getUrlAliasService()->removeAliasesForLocation( $location );

        $contentService = $this->repository->getContentService();

        unset( $this->locations[$location->id] );

        if ( !$this->hasLocation( $location->contentInfo ) )
        {
            $contentService->deleteContent( $location->contentInfo );
        }

        foreach ( $this->loadLocationChildren( $location )->locations as $child )
        {
            $this->deleteLocation( $child );
        }
    }

    /**
     * Returns if a location for the given $contentInfo exists.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return boolean
     */
    protected function hasLocation( ContentInfo $contentInfo )
    {
        foreach ( $this->locations as $location )
        {
            if ( $location->getContentInfo() == null )
            {
                // Skip root location
                continue;
            }
            if ( $location->getContentInfo()->id == $contentInfo->id )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * Only the items on which the user has read access are copied.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  if the target location is a sub location of the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \eZ\Publish\API\Repository\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location The newly created location of the copied subtree
     *
     * @todo enhancement - this method should return a result structure containing the new location and a list
     *       of locations which are not copied due to permission denials.
     */
    public function copySubtree( Location $subtree,  Location $targetParentLocation )
    {
        // Check permissions
        if ( false === $this->repository->canUser( 'content', 'edit', $subtree, $targetParentLocation ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        // Check new parent is not tree
        $this->checkLocationNotInTree( $subtree, $targetParentLocation );

        return $this->copySubtreeInternal( $subtree, $targetParentLocation );
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * Only the items on which the user has read access are copied.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \eZ\Publish\API\Repository\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location The newly created location of the copied subtree
     */
    private function copySubtreeInternal( Location $subtree,  Location $targetParentLocation )
    {
        $values = array_merge(
            $this->locationToArray( $subtree ),
            array(
                'id' => ++$this->nextLocationId,
                'remoteId' => md5( uniqid( $subtree->remoteId, true ) ),
                'depth' => $targetParentLocation->depth + 1,
                'parentLocationId' => $targetParentLocation->id,
                'pathString' => "{$targetParentLocation->pathString}{$this->nextLocationId}/"
            )
        );

        $this->locations[$values['id']] = new LocationStub( $values );

        foreach ( $this->loadLocationChildren( $subtree )->locations as $childLocation )
        {
            $this->copySubtreeInternal( $childLocation, $this->locations[$values['id']] );
        }

        return $this->locations[$values['id']];
    }

    /**
     * Checks if the given <b>$location</b> is not a child of the given <b>$substree</b>.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return void
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function checkLocationNotInTree( Location $subtree, Location $location )
    {
        if ( $subtree->id === $location->id )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }
        foreach ( $this->loadLocationChildren( $subtree )->locations as $childLocation )
        {
            $this->checkLocationNotInTree( $childLocation, $location );
        }
    }

    /**
     * Moves the subtree to $newParentLocation
     *
     * If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to move this location to the target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    public function moveSubtree( Location $location, Location $newParentLocation )
    {
        if ( false === $this->repository->canUser( 'content', 'move', $location, $newParentLocation ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $oldParentLocation = $this->loadLocation( $location->parentLocationId );

        $this->moveSubtreeInternal( $location, $newParentLocation );
    }

    /**
     * Moves the subtree to $newParentLocation
     *
     * If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to move this location to the target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    private function moveSubtreeInternal( Location $location, Location $newParentLocation )
    {
        $values = array_merge(
            $this->locationToArray( $location ),
            array(
                'depth' => $newParentLocation->depth + 1,
                'pathString' => "{$newParentLocation->pathString}{$location->id}/",
                'parentLocationId' => $newParentLocation->id,
            )
        );

        $newLocation = $this->locations[$location->id] = new LocationStub( $values );
        $this->repository->getUrlAliasService()->createAliasesForLocation( $newLocation );

        foreach ( $this->loadLocationChildren( $location )->locations as $childLocation )
        {
            $this->moveSubtreeInternal(
                $childLocation,
                $this->locations[$location->id]
            );
        }
    }

    /**
     * Internal helper method used to trash a location tree.
     *
     * @access private
     *
     * @internal
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $trashed
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function trashLocation( Location $location, array $trashed = array() )
    {
        $this->repository->getUrlAliasService()->removeAliasesForLocation( $location );

        $trashed[] = $location;
        foreach ( $this->loadLocationChildren( $location )->locations as $childLocation )
        {
            $trashed = $this->trashLocation( $childLocation, $trashed );
        }

        unset( $this->locations[$location->id] );

        return $trashed;
    }

    /**
     * Internal helper method used to recover a location tree.
     *
     * @access private
     *
     * @internal
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentlocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function recoverLocation( Location $location, Location $newParentlocation = null )
    {
        if ( $newParentlocation )
        {
            $location->setParentLocationId( $newParentlocation->id );
        }

        $location->setDepth(
            $this->locations[$location->parentLocationId]->depth + 1
        );
        $location->setPathString(
            $this->locations[$location->parentLocationId]->pathString . $location->id . "/"
        );

        // If the main location of the restored content is also trashed /
        // deleted
        if ( !isset( $this->locations[$location->getContentInfo()->mainLocationId] ) )
        {
            $location->getContentInfo()->setMainLocationId( $location->id );
        }

        $this->repository->getUrlAliasService()->createAliasesForLocation( $location );

        return ( $this->locations[$location->id] = $location );
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @access private
     *
     * @internal
     *
     * @return void
     */
    public function rollback()
    {
        $this->initFromFixture();
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        $this->locations = array();
        $this->nextLocationId = 0;

        list(
            $locations,
            $this->nextLocationId
        ) = $this->repository->loadFixture( 'Location' );

        foreach ( $locations as $location )
        {
            $this->locations[$location->id] = $location;
            $this->nextLocationId = max( $this->nextLocationId, $location->id );
        }
    }
}

