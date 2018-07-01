<?php

/**
 * File containing the TrashHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler as TrashHandlerInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Relation;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
 */
class TrashHandler extends AbstractHandler implements TrashHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadTrashItem($id)
    {
        $this->logger->logCall(__METHOD__, array('id' => $id));

        return $this->persistenceHandler->trashHandler()->loadTrashItem($id);
    }

    /**
     * {@inheritdoc}
     */
    public function trashSubtree($locationId)
    {
        $this->logger->logCall(__METHOD__, array('locationId' => $locationId));

        $location = $this->persistenceHandler->locationHandler()->load($locationId);
        $reverseRelations = $this->persistenceHandler->contentHandler()->loadRelations($location->contentId);

        $return = $this->persistenceHandler->trashHandler()->trashSubtree($locationId);

        $tags = [];
        if (!empty($reverseRelations)) {
            $tags = array_map(function (Relation $relation) {
                return 'content-fields-' . $relation->destinationContentId;
            }, $reverseRelations);
        }

        $this->cache->invalidateTags([
            'content-' . $location->contentId,
            'content-fields-' . $location->contentId,
            'location-' . $locationId, 'location-path-' . $locationId,
        ] + $tags);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function recover($trashedId, $newParentId)
    {
        $this->logger->logCall(__METHOD__, array('id' => $trashedId, 'newParentId' => $newParentId));

        $return = $this->persistenceHandler->trashHandler()->recover($trashedId, $newParentId);

        $location = $this->persistenceHandler->locationHandler()->load($return);
        $reverseRelations = $this->persistenceHandler->contentHandler()->loadRelations($location->contentId);

        $tags = [];
        if (!empty($reverseRelations)) {
            $tags = array_map(function (Relation $relation) {
                return 'content-fields-' . $relation->destinationContentId;
            }, $reverseRelations);
        }

        $this->cache->invalidateTags([
            'content-' . $location->contentId,
            'content-fields-' . $location->contentId,
            'location-' . $trashedId, 'location-path-' . $trashedId,
        ] + $tags);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function findTrashItems(Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null)
    {
        $this->logger->logCall(__METHOD__, array('criterion' => $criterion ? get_class($criterion) : 'null'));

        return $this->persistenceHandler->trashHandler()->findTrashItems($criterion, $offset, $limit, $sort);
    }

    /**
     * {@inheritdoc}
     */
    public function emptyTrash()
    {
        $this->logger->logCall(__METHOD__, array());
        $this->persistenceHandler->trashHandler()->emptyTrash();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTrashItem($trashedId)
    {
        $this->logger->logCall(__METHOD__, array('id' => $trashedId));
        $this->persistenceHandler->trashHandler()->deleteTrashItem($trashedId);
    }
}
