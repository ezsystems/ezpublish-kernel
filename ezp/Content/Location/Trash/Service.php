<?php
/**
 * File containing the ezp\Content\Location\Service class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location\Trash;
use ezp\Base\Exception,
    ezp\Base\Service as BaseService,
    ezp\Content\Location,
    ezp\Content\Location\Trashed,
    ezp\Content\Location\Collection,
    ezp\Content\Location\Trash\Exception\NotFound as TrashedLocationNotFound,
    ezp\Content\Query,
    ezp\Base\Proxy,
    ezp\Base\Exception\NotFound,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\Logic,
    ezp\Persistence\Content\Location\Trashed as TrashedLocationValue,
    ezp\Persistence\ValueObject;

/**
 * Location service, used for complex subtree operations
 */
class Service extends BaseService
{
    /**
     * Loads a trashed location object from its $id
     * @param integer $id
     * @return \ezp\Content\Location\Trashed
     * @throws \ezp\Content\Location\Trash\Exception\NotFound if no trashed location is available with $id
     */
    public function load( $id )
    {
        try
        {
            return $this->buildDomainObject( $this->handler->trashHandler()->load( $id ) );
        }
        catch ( NotFound $e )
        {
            throw new TrashedLocationNotFound( $id, $e );
        }
    }

    /**
     * Loads a trashed location object from original $locationId
     * @param integer $locationId
     * @return \ezp\Content\Location\Trashed
     * @throws \ezp\Content\Location\Trash\Exception\NotFound if no trashed location is available with $locationId
     */
    public function loadByLocationId( $locationId )
    {
        try
        {
            return $this->buildDomainObject(
                $this->handler->trashHandler()->loadFromLocationId( $locationId )
            );
        }
        catch ( NotFound $e )
        {
            throw new TrashedLocationNotFound( $e->identifier, $e );
        }
    }

    /**
     * Sends $location and all its children to trash and returns trashed location object
     * Content is left untouched.
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\Location\Trashed
     * @todo Refresh $location (with an Identity map ?)
     */
    public function trash( Location $location )
    {
        $trashedLocationVo = $this->handler->trashHandler()->trashSubtree( $location->id );
        return $this->buildDomainObject( $trashedLocationVo );
    }

    /**
     * Restores $trashedLocation at its original place if possible.
     * If $newParentLocation is provided, $trashedLocation will be restored under it.
     * Will throw an exception if new/original parent is not available any more.
     *
     * @param \ezp\Content\Location\Trashed $trashedLocation
     * @return \ezp\Content\Location
     * @throws \ezp\Content\Location\Exception\ParentNotFound
     */
    public function untrash( Trashed $trashedLocation, Location $newParentLocation = null )
    {
        $newParentId = $trashedLocation->parentId;
        if ( isset( $newParentLocation ) )
            $newParentId = $newParentLocation->id;

        $locationId = $this->handler->trashHandler()->untrashLocation( $trashedLocation->id, $newParentId );
        return $this->repository->getLocationService()->load( $locationId );
    }

    /**
     * Empties trash.
     * All location/content contained in the trash will be removed
     */
    public function emptyTrash()
    {
        $this->handler->trashHandler()->emptyTrash();
    }

    /**
     * Deletes $trashedLocation from trash
     * Content will be removed
     *
     * @param \ezp\Content\Location\Trashed Trashed location to delete from trash
     */
    public function emptyOne( Trashed $trashedLocation )
    {
        $this->handler->trashHandler()->emptyOne( $trashedLocation->id );
    }

    /**
     * Returns a collection of Trashed locations contained in the trash.
     * $query allows to filter/sort the elements to be contained in the collection.
     * <code>
     * $qb = new ezp\Content\Query\Builder();
     * $qb->addCriteria(
     *     $qb->contentTypeId->eq( 'blog_post' ),
     *     $qb->field->eq( 'author', 'community@ez.no' )
     * )->addSortClause(
     *     $qb->sort->dateCreated( Query::SORT_DESC )
     * )->setOffset( 0 )->setLimit( 15 );
     * $trashList = $trashService->getList( $qb->getQuery() );
     * </code>
     *
     * @param \ezp\Content\Query $query
     * @return \ezp\Content\Location\Collection
     */
    public function getList( Query $query )
    {
        $result = $this->handler->trashHandler()->listTrashed(
            $query->criterion,
            $query->offset,
            $query->limit,
            $query->sortClauses
        );

        $aTrashed = array();
        foreach ( $result as $trashedVo )
        {
            $aTrashed[] = $this->buildDomainObject( $trashedVo );
        }

        return new Collection( $aTrashed );
    }

    /**
     * Builds Trashed location domain object from $vo ValueObject returned by Persistence API
     * @param \ezp\Persistence\Location\Trashed $vo Location value object (extending \ezp\Persistence\ValueObject)
     *                                      returned by persistence
     * @return \ezp\Content\Location\Trashed
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    protected function buildDomainObject( TrashedLocationValue $vo )
    {
        $trashedLocation = new Trashed( new Proxy( $this->repository->getContentService(), $vo->contentId ) );
        $trashedLocation->setState(
            array(
                'parent' => new Proxy( $this, $vo->parentId ),
                'properties' => $vo
            )
        );

        return $trashedLocation;
    }
}
