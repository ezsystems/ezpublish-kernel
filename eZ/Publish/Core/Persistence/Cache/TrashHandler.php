<?php

/**
 * File containing the TrashHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
    public function loadTrashItem($id)
    {
        $this->logger->logCall(__METHOD__, array('id' => $id));

        return $this->persistenceHandler->trashHandler()->loadTrashItem($id);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashSubtree($locationId)
    {
        $this->logger->logCall(__METHOD__, array('locationId' => $locationId));
        $return = $this->persistenceHandler->trashHandler()->trashSubtree($locationId);
        $this->cache->clear('location');//TIMBER!
        $this->cache->clear('content');//TIMBER!
        $this->cache->clear('user', 'role', 'assignments', 'byGroup');

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function recover($trashedId, $newParentId)
    {
        $this->logger->logCall(__METHOD__, array('id' => $trashedId, 'newParentId' => $newParentId));
        $return = $this->persistenceHandler->trashHandler()->recover($trashedId, $newParentId);
        $this->cache->clear('location', 'subtree');
        $this->cache->clear('content');//TIMBER!
        $this->cache->clear('user', 'role', 'assignments', 'byGroup');

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function findTrashItems(Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null)
    {
        $this->logger->logCall(__METHOD__, array('criterion' => get_class($criterion)));

        return $this->persistenceHandler->trashHandler()->findTrashItems($criterion, $offset, $limit, $sort);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function emptyTrash()
    {
        $this->logger->logCall(__METHOD__, array());
        $this->persistenceHandler->trashHandler()->emptyTrash();
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function deleteTrashItem($trashedId)
    {
        $this->logger->logCall(__METHOD__, array('id' => $trashedId));
        $this->persistenceHandler->trashHandler()->deleteTrashItem($trashedId);
    }
}
