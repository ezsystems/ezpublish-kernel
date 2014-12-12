<?php
/**
 * File containing the TrashHandler implementation
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler as TrashHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Trashed;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
 */
class TrashHandler extends AbstractHandler implements TrashHandlerInterface
{
    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function loadTrashItem( $id )
    {
        $this->logger->logCall( __METHOD__, array( 'id' => $id ) );
        return $this->persistenceHandler->trashHandler()->loadTrashItem( $id );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashSubtree( Location $location )
    {
        $this->logger->logCall( __METHOD__, array( 'locationId' => $location->id ) );
        $return = $this->persistenceHandler->trashHandler()->trashSubtree( $location );
        $this->cache->clear( 'location', $location->id );
        $this->cache->clear( 'location', 'subtree' );
        $this->cache->clear( 'content', $location->contentId );
        $this->cache->clear( 'content', 'info', $location->contentId );
        $this->cache->clear( 'content', 'locations', $location->contentId );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup' );
        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function recover( Trashed $trashed, Location $newParent )
    {
        $this->logger->logCall( __METHOD__, array( 'id' => $trashed->id, 'newParentId' => $newParent->id ) );
        $return = $this->persistenceHandler->trashHandler()->recover( $trashed, $newParent );
        $this->cache->clear( 'location', 'subtree' );
        $this->cache->clear( 'content', $trashed->contentId );
        $this->cache->clear( 'content', 'info', $trashed->contentId );
        $this->cache->clear( 'content', 'locations', $trashed->contentId );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup' );
        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function findTrashItems( Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null )
    {
        $this->logger->logCall( __METHOD__, array( 'criterion' => get_class( $criterion ) ) );
        return $this->persistenceHandler->trashHandler()->findTrashItems( $criterion, $offset, $limit, $sort );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function emptyTrash()
    {
        $this->logger->logCall( __METHOD__, array() );
        $this->persistenceHandler->trashHandler()->emptyTrash();
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function deleteTrashItem( Trashed $trashed )
    {
        $this->logger->logCall( __METHOD__, array( 'id' => $trashed->id ) );
        $this->persistenceHandler->trashHandler()->deleteTrashItem( $trashed );
        $this->cache->clear( 'content', $trashed->contentId );
        $this->cache->clear( 'content', 'info', $trashed->contentId );
        $this->cache->clear( 'content', 'info', 'remoteId' );
        $this->cache->clear( 'content', 'locations', $trashed->contentId );
    }
}
