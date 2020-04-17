<?php

/**
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
    private const EMPTY_TRASH_BULK_SIZE = 100;

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
                return 'content-' . $relation->destinationContentId;
            }, $reverseRelations);
        }

        $tags = array_merge(
            [
                'content-' . $location->contentId,
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
                return 'content-' . $relation->destinationContentId;
            }, $reverseRelations);
        }

        $tags = array_merge(
            [
                'content-' . $location->contentId,
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
        $tags = [];
        $offset = 0;
        do {
            $trashedItems = $this->persistenceHandler->trashHandler()->findTrashItems(null, $offset, self::EMPTY_TRASH_BULK_SIZE);
            foreach ($trashedItems as $trashedItem) {
                $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations($trashedItem->contentId);

                foreach ($reverseRelations as $relation) {
                    $tags['content-' . $relation->sourceContentId] = true;
                }
                $tags['content-' . $trashedItem->contentId] = true;
                $tags['location-path-' . $trashedItem->id] = true;
            }
            $offset += self::EMPTY_TRASH_BULK_SIZE;
            // Once offset is larger then total count we can exit
        } while ($trashedItems->totalCount > $offset);

        $return = $this->persistenceHandler->trashHandler()->emptyTrash();

        if (!empty($tags)) {
            $this->cache->invalidateTags(array_keys($tags));
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
            return 'content-' . $relation->sourceContentId;
        }, $reverseRelations);

        $return = $this->persistenceHandler->trashHandler()->deleteTrashItem($trashedId);

        $tags = array_merge(
            [
                'content-' . $return->contentId,
                'location-path-' . $trashedId,
            ],
            $relationTags
        );
        $this->cache->invalidateTags(array_values(array_unique($tags)));

        return $return;
    }
}
