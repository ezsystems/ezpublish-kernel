<?php
/**
 * File containing the TrashHandler implementation
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler as TrashHandlerInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

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
        return $this->persistenceFactory->getTrashHandler()->loadTrashItem( $id );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashSubtree( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'locationId' => $locationId ) );
        $this->cache->clear( 'location' );//TIMBER!
        $this->cache->clear( 'content' );//TIMBER!
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup' );
        return $this->persistenceFactory->getTrashHandler()->trashSubtree( $locationId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function recover( $trashedId, $newParentId )
    {
        $this->logger->logCall( __METHOD__, array( 'id' => $trashedId, 'newParentId' => $newParentId ) );
        $this->cache->clear( 'location', 'subtree' );
        $this->cache->clear( 'user', 'role', 'assignments', 'byGroup' );
        return $this->persistenceFactory->getTrashHandler()->recover( $trashedId, $newParentId );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function findTrashItems( Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null )
    {
        $this->logger->logCall( __METHOD__, array( 'criterion' => get_class( $criterion ) ) );
        return $this->persistenceFactory->getTrashHandler()->findTrashItems( $criterion, $offset, $limit, $sort );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function emptyTrash()
    {
        $this->logger->logCall( __METHOD__, array() );
        $this->persistenceFactory->getTrashHandler()->emptyTrash();
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function deleteTrashItem( $trashedId )
    {
        $this->logger->logCall( __METHOD__, array( 'id' => $trashedId ) );
        $this->persistenceFactory->getTrashHandler()->deleteTrashItem( $trashedId );
    }
}
