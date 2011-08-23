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
    ezp\Content\Proxy,
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
     * @return \ezp\Content\Location\Trash
     * @throws \ezp\Base\Exception\NotFound if no location is available with $locationId
     */
    public function load( $id )
    {
        $trashedLocationVO = $this->handler->locationHandler()->load( $id );
        if ( !$trashedLocationVO instanceof LocationValue )
        {
            throw new NotFound( 'TrashedLocation', $id );
        }

        return $this->buildDomainObject( $trashedLocationVO );
    }

    /**
     * Sends $location and all its children to trash and returns trashed location object
     * Content is left untouched.
     * @param \ezp\Content\Location $location
     * @return \ezp\Content\TrashedLocation
     */
    public function trash( Location $location )
    {
        $trashedLocationVo = $this->handler->locationHandler()->trashSubtree( $location->id );
        $this->refreshDomainObject( $location );
        return $this->buildDomainObject( $trashedLocationVo );
    }

    public function untrash( Location $location )
    {
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
