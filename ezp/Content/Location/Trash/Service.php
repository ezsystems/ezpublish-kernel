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
     * @throws \ezp\Base\Exception\NotFound if no trashed location is available with $id
     */
    public function load( $id )
    {
        $trashedLocationVO = $this->handler->trashHandler()->load( $id );
        if ( !$trashedLocationVO instanceof TrashedLocationValue )
        {
            throw new NotFound( 'TrashedLocation', $id );
        }

        return $this->buildDomainObject( $trashedLocationVO );
    }

    /**
     * Loads a trashed location object from original $locationId
     * @param integer $locationId
     * @return \ezp\Content\Location\Trashed
     * @throws \ezp\Base\Exception\NotFound if no trashed location is available with $locationId
     */
    public function loadByLocationId( $locationId )
    {
        $trashedLocationVO = $this->handler->trashHandler()->loadFromLocationId( $locationId );
        if ( !$trashedLocationVO instanceof TrashedLocationValue )
        {
            throw new NotFound( 'TrashedLocation', $locationId );
        }

        return $this->buildDomainObject( $trashedLocationVO );
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
     * Restores $location at its original place if possible.
     * Will throw an exception if original place is not available any more.
     *
     * @param \ezp\Content\LocationLocation $location
     * @throws \ezp\Base\Exception\Logic
     */
    public function untrash( Location $location )
    {
        throw new \RuntimeException( 'Not implemented yet' );
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
     */
    public function emptyOne( Trashed $trashedLocation )
    {
        $this->handler->trashHandler()->emptyOne( $trashedLocation->id );
    }

    /**
     * Builds Location domain object from $vo ValueObject returned by Persistence API
     * @param \ezp\Persistence\Location\Trashed $vo Location value object (extending \ezp\Persistence\ValueObject)
     *                                      returned by persistence
     * @return \ezp\Content\Location\Trashed
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    protected function buildDomainObject( TrashedLocationValue $vo )
    {
        $trashedLocation = new Trashed( new Proxy( $this->repository->getContentService(), $vo->contentId ) );

        return $this->refreshDomainObject( $trashedLocation, $vo );
    }

    /**
     * Refreshes provided $trashedLocation. Useful if backend data has changed
     *
     * @param \ezp\Content\Location\Trashed $trashedLocation Trashed location to refresh
     * @param \ezp\Persistence\Location\Trashed $vo Trashed location value object.
     *                                              If provided, $trashedLocation will be updated with $vo's data
     * @return \ezp\Content\Location\Trashed
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    protected function refreshDomainObject( Trashed $trashedLocation, TrashedLocationValue $vo = null )
    {
        if ( $vo === null )
        {
            $vo = $this->handler->trashHandler()->load( $trashedLocation->id );
        }

        $newState = array(
            'parent' => new Proxy( $this, $vo->parentId ),
            'properties' => $vo
        );
        // Check if associated content also needs to be refreshed
        if ( $vo->contentId != $trashedLocation->contentId )
        {
            $newState['content'] = new Proxy( $this->repository->getContentService(), $vo->contentId );
        }
        $trashedLocation->setState( $newState );

        return $trashedLocation;
    }
}
