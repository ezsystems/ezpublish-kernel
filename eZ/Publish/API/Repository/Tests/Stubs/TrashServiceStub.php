<?php
/**
 * File containing the TrashServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\TrashService;
use \eZ\Publish\API\Repository\Values\Content\Query;
use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\SearchResult;
use \eZ\Publish\API\Repository\Values\Content\TrashItem;

use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\TrashItemStub;

/**
 * Trash service used for content/location trash handling.
 *
 * @package eZ\Publish\API\Repository
 */
class TrashServiceStub implements TrashService
{
    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\TrashItemStub[]
     */
    private $trashItems = array();

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location[][]
     */
    private $locations = array();

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\LocationServiceStub
     */
    private $locationService;

    /**
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     * @param \eZ\Publish\API\Repository\Tests\Stubs\LocationServiceStub $locationService
     */
    public function __construct( RepositoryStub $repository, LocationServiceStub $locationService )
    {
        $this->repository      = $repository;
        $this->locationService = $locationService;
    }

    /**
     * Loads a trashed location object from its $id.
     *
     * Note that $id is identical to original location, which has been previously trashed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to read the trashed location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the location with the given id does not exist
     *
     * @param integer $trashItemId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem( $trashItemId )
    {
        if ( false === isset( $this->trashItems[$trashItemId] ) )
        {
            throw new NotFoundExceptionStub( 'What error code should be used?' );
        }
        if ( false === $this->repository->canUser( 'content', 'edit', $this->locations[$trashItemId][$trashItemId] ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return $this->trashItems[$trashItemId];
    }

    /**
     * Sends $location and all its children to trash and returns the corresponding trash item.
     *
     * Content is left untouched.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to trash the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function trash( Location $location )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $location ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->locations[$location->id] = array();
        foreach ( $this->locationService->__trashLocation( $location ) as $trashedLocation )
        {
            $this->locations[$location->id][$trashedLocation->id] = $trashedLocation;
        }

        $this->trashItems[$location->id] = new TrashItemStub(
            array(
                'id'                       =>  $location->id,
                'childCount'               =>  $location->childCount,
                'depth'                    =>  $location->depth,
                'hidden'                   =>  $location->hidden,
                'invisible'                =>  $location->invisible,
                'modifiedSubLocationDate'  =>  $location->modifiedSubLocationDate,
                'parentLocationId'         =>  $location->parentLocationId,
                'pathString'               =>  $location->pathString,
                'priority'                 =>  $location->priority,
                'remoteId'                 =>  $location->remoteId,
                'sortField'                =>  $location->sortField,
                'sortOrder'                =>  $location->sortOrder,

                'location'                 =>  $location
            )
        );

        return $this->trashItems[$location->id];
    }

    /**
     * Recovers the $trashedLocation at its original place if possible.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to recover the trash item at the parent location location
     *
     * If $newParentLocation is provided, $trashedLocation will be restored under it.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $newParentLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created or recovered location
     */
    public function recover( TrashItem $trashItem, Location $newParentLocation = null )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $trashItem ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $location = $this->locationService->__recoverLocation(
            $this->locations[$trashItem->id][$trashItem->id],
            $newParentLocation
        );

        unset( $this->locations[$trashItem->id][$trashItem->id] );

        foreach ( $this->locations[$trashItem->id] as $descendantLocation )
        {
            $this->locationService->__recoverLocation(
                $descendantLocation,
                ( $descendantLocation->parentLocationId == $location->id
                    ? $location
                    : null )
            );
        }

        unset(
            $this->trashItems[$trashItem->id],
            $this->locations[$trashItem->id]
        );

        return $location;
    }

    /**
     * Empties trash.
     *
     * All locations contained in the trash will be removed. Content objects will be removed
     * if all locations of the content are gone.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to empty the trash
     */
    public function emptyTrash()
    {
        if ( false === $this->repository->hasAccess( 'content', 'remove' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->trashItems = array();
        $this->locations  = array();
    }

    /**
     * Deletes a trash item.
     *
     * The corresponding content object will be removed
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to delete this trash item
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     */
    public function deleteTrashItem( TrashItem $trashItem )
    {
        if ( false === $this->repository->canUser( 'content', 'remove', $trashItem ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        unset(
            $this->trashItems[$trashItem->id],
            $this->locations[$trashItem->id]
        );
    }

    /**
     * Returns a collection of Trashed locations contained in the trash.
     *
     * $query allows to filter/sort the elements to be contained in the collection.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SearchResult
     */
    public function findTrashItems( Query $query )
    {
        $contentService = $this->repository->getContentService();

        $trashItems   = array();
        $searchResult = $contentService->findContent( $query, array() );

        /* @var $item \eZ\Publish\API\Repository\Values\Content\Content */
        foreach ( $searchResult->items as $item )
        {
            $locationId = $item->contentInfo->mainLocationId;
            foreach ( $this->locations as $trashItemId => $locations )
            {
                if ( false === isset( $locations[$locationId] ) )
                {
                    continue;
                }
                $trashItems[$trashItemId] = $this->trashItems[$trashItemId];
            }
        }

        return new SearchResult(
            array(
                'query'  =>  $query,
                'count'  =>  count( $trashItems ),
                'items'  =>  array_values( $trashItems ),
            )
        );
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @return void
     */
    public function __rollback()
    {
        $this->emptyTrash();
    }
}
