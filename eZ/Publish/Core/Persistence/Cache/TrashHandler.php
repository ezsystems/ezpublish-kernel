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
        $this->logger->logCall(__METHOD__, ['id' => $id]);

        return $this->persistenceHandler->trashHandler()->loadTrashItem($id);
    }

    /**
     * {@inheritdoc}
     */
    public function trashSubtree($locationId)
    {
        $this->logger->logCall(__METHOD__, ['locationId' => $locationId]);

        $location = $this->persistenceHandler->locationHandler()->load($locationId);
        $reverseRelations = $this->persistenceHandler->contentHandler()->loadRelations($location->contentId);

        $return = $this->persistenceHandler->trashHandler()->trashSubtree($locationId);

        $relationTags = [];
        if (!empty($reverseRelations)) {
            $relationTags = array_map(function (Relation $relation) {
                return 'content-fields-' . $relation->destinationContentId;
            }, $reverseRelations);
        }

        $tags = array_merge(
            [
                'content-' . $location->contentId,
                'content-fields-' . $location->contentId,
                'location-' . $locationId,
                'location-path-' . $locationId,
            ],
            $relationTags
        );
        $this->cache->invalidateTags(array_values(array_unique($tags)));

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function recover($trashedId, $newParentId)
    {
        $this->logger->logCall(__METHOD__, ['id' => $trashedId, 'newParentId' => $newParentId]);

        $return = $this->persistenceHandler->trashHandler()->recover($trashedId, $newParentId);

        $location = $this->persistenceHandler->locationHandler()->load($return);
        $reverseRelations = $this->persistenceHandler->contentHandler()->loadRelations($location->contentId);

        $relationTags = [];
        if (!empty($reverseRelations)) {
            $relationTags = array_map(function (Relation $relation) {
                return 'content-fields-' . $relation->destinationContentId;
            }, $reverseRelations);
        }

        $tags = array_merge(
            [
                'content-' . $location->contentId,
                'content-fields-' . $location->contentId,
                'location-' . $trashedId,
                'location-path-' . $trashedId,
            ],
            $relationTags
        );
        $this->cache->invalidateTags(array_values(array_unique($tags)));

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function findTrashItems(Criterion $criterion = null, $offset = 0, $limit = null, array $sort = null)
    {
        $this->logger->logCall(__METHOD__, ['criterion' => $criterion ? get_class($criterion) : 'null']);

        return $this->persistenceHandler->trashHandler()->findTrashItems($criterion, $offset, $limit, $sort);
    }

    /**
     * {@inheritdoc}
     */
    public function emptyTrash()
    {
        $this->logger->logCall(__METHOD__, []);

        // We can not use the return value of emptyTrash method because, in the next step, we are not able
        // to fetch the reverse relations of deleted content.
        $trashedItems = $this->persistenceHandler->trashHandler()->findTrashItems();

        $tags = [];
        foreach ($trashedItems as $trashedItem) {
            $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations($trashedItem->contentId);

            foreach ($reverseRelations as $relation) {
                $tags[] = 'content-fields-' . $relation->sourceContentId;
            }
            $tags[] = 'content-' . $trashedItem->contentId;
            $tags[] = 'content-fields-' . $trashedItem->contentId;
            $tags[] = 'location-' . $trashedItem->id;
            $tags[] = 'location-path-' . $trashedItem->id;
        }

        $return = $this->persistenceHandler->trashHandler()->emptyTrash();

        if (!empty($tags)) {
            $this->cache->invalidateTags(array_values(array_unique($tags)));
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTrashItem($trashedId)
    {
        $this->logger->logCall(__METHOD__, ['id' => $trashedId]);

        // We can not use the return value of deleteTrashItem method because, in the next step, we are not able
        // to fetch the reverse relations of deleted content.
        $trashed = $this->persistenceHandler->trashHandler()->loadTrashItem($trashedId);

        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations($trashed->contentId);

        $relationTags = array_map(function (Relation $relation) {
            return 'content-fields-' . $relation->sourceContentId;
        }, $reverseRelations);

        $return = $this->persistenceHandler->trashHandler()->deleteTrashItem($trashedId);

        $tags = array_merge(
            [
                'content-' . $return->contentId,
                'content-fields-' . $return->contentId,
                'location-' . $trashedId,
                'location-path-' . $trashedId,
            ],
            $relationTags
        );
        $this->cache->invalidateTags(array_values(array_unique($tags)));

        return $return;
    }
}
