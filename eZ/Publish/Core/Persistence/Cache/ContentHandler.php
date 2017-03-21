<?php

/**
 * File containing the ContentHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Handler
 */
class ContentHandler extends AbstractHandler implements ContentHandlerInterface
{
    const ALL_TRANSLATIONS_KEY = '0';

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        // Cached on demand when published or loaded
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        return $this->persistenceHandler->contentHandler()->create($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function createDraftFromVersion($contentId, $srcVersion, $userId)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $srcVersion, 'user' => $userId));

        return $this->persistenceHandler->contentHandler()->createDraftFromVersion($contentId, $srcVersion, $userId);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($contentId, $versionNo = null)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo));

        return $this->persistenceHandler->contentHandler()->copy($contentId, $versionNo);
    }

    /**
     * {@inheritdoc}
     */
    public function load($contentId, $versionNo, array $translations = null)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $cacheItem = $this->cache->getItem("ez-content-${contentId}-${versionNo}-${translationsKey}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo, 'translations' => $translations));
        $content = $this->persistenceHandler->contentHandler()->load($contentId, $versionNo, $translations);
        $cacheItem->set($content);
        $cacheItem->tag($this->getCacheTags($content->versionInfo->contentInfo, true));
        $this->cache->save($cacheItem);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfo($contentId)
    {
        $cacheItem = $this->cache->getItem("ez-content-info-${contentId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('content' => $contentId));
        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($contentId);
        $cacheItem->set($contentInfo);
        $cacheItem->tag($this->getCacheTags($contentInfo));
        $this->cache->save($cacheItem);

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        $cacheItem = $this->cache->getItem("ez-content-info-byRemoteId-${remoteId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('content' => $remoteId));
        $contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfoByRemoteId($remoteId);
        $cacheItem->set($contentInfo);
        $cacheItem->tag($this->getCacheTags($contentInfo));
        $this->cache->save($cacheItem);

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function loadVersionInfo($contentId, $versionNo)
    {
        $cacheItem = $this->cache->getItem("ez-content-version-info-${contentId}-${versionNo}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $versionNo]);
        $versionInfo = $this->persistenceHandler->contentHandler()->loadVersionInfo($contentId, $versionNo);
        $cacheItem->set($versionInfo);
        $cacheItem->tag($this->getCacheTags($versionInfo->contentInfo));
        $this->cache->save($cacheItem);

        return $versionInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function loadDraftsForUser($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->contentHandler()->loadDraftsForUser($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($contentId, $status, $versionNo)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'status' => $status, 'version' => $versionNo));
        $return = $this->persistenceHandler->contentHandler()->setStatus($contentId, $status, $versionNo);

        $this->cache->deleteItem("ez-content-version-info-${contentId}-${versionNo}");
        if ($status === VersionInfo::STATUS_PUBLISHED) {
            $this->cache->invalidateTags(['content-' . $contentId]);
        } else {
            $this->cache->invalidateTags(["content-$contentId-version-list"]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata($contentId, MetadataUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'struct' => $struct));
        $contentInfo = $this->persistenceHandler->contentHandler()->updateMetadata($contentId, $struct);
        $this->cache->invalidateTags(['content-' . $contentId]);

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function updateContent($contentId, $versionNo, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo, 'struct' => $struct));
        $content = $this->persistenceHandler->contentHandler()->updateContent($contentId, $versionNo, $struct);
        $this->cache->invalidateTags(['content-' . $contentId]);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContent($contentId)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId));

        // Load reverse field relations first
        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations(
            $contentId,
            APIRelation::FIELD
        );

        $return = $this->persistenceHandler->contentHandler()->deleteContent($contentId);

        $this->cache->invalidateTags(['content-' . $contentId]);
        if (!empty($reverseRelations)) {
            $this->cache->invalidateTags(
                array_map(
                    function ($relation) {
                        // only the full content object *with* fields is affected by this
                        return 'content-fields-' . $relation->sourceContentId;
                    },
                    $reverseRelations
                )
            );
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteVersion($contentId, $versionNo)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo));
        $return = $this->persistenceHandler->contentHandler()->deleteVersion($contentId, $versionNo);
        $this->cache->invalidateTags(['content-' . $contentId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function listVersions($contentId, $status = null, $limit = -1)
    {
        $cacheItem = $this->cache->getItem("ez-content-${contentId}-version-list" . ($status ? "-byStatus-${status}" : ''));
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'status' => $status));
        $versions = $this->persistenceHandler->contentHandler()->listVersions($contentId, $status, $limit);
        $cacheItem->set($versions);
        $tags = ["content-$contentId", "content-$contentId-version-list"];
        $cacheItem->tag(empty($versions) ? $tags : $this->getCacheTags($versions[0]->contentInfo, false, $tags));
        $this->cache->save($cacheItem);

        return $versions;
    }

    /**
     * {@inheritdoc}
     */
    public function addRelation(RelationCreateStruct $relation)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $relation));

        return $this->persistenceHandler->contentHandler()->addRelation($relation);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRelation($relationId, $type)
    {
        $this->logger->logCall(__METHOD__, array('relation' => $relationId, 'type' => $type));
        $this->persistenceHandler->contentHandler()->removeRelation($relationId, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRelations($sourceContentId, $sourceContentVersionNo = null, $type = null)
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'content' => $sourceContentId,
                'version' => $sourceContentVersionNo,
                'type' => $type,
            )
        );

        return $this->persistenceHandler->contentHandler()->loadRelations($sourceContentId, $sourceContentVersionNo, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReverseRelations($destinationContentId, $type = null)
    {
        $this->logger->logCall(__METHOD__, array('content' => $destinationContentId, 'type' => $type));

        return $this->persistenceHandler->contentHandler()->loadReverseRelations($destinationContentId, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function publish($contentId, $versionNo, MetadataUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo, 'struct' => $struct));
        $content = $this->persistenceHandler->contentHandler()->publish($contentId, $versionNo, $struct);
        $this->cache->invalidateTags(['content-' . $contentId]);

        return $content;
    }

    /**
     * Return relevant content and location tags so cache can be purged reliably.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
     * @param bool $withFields Set to true if item contains fields which should be expired on relation or type updates.
     * @param array $tags Optional, can be used to specify other tags.
     *
     * @return array
     */
    private function getCacheTags(ContentInfo $contentInfo, $withFields = false, array $tags = [])
    {
        $tags[] = 'content-' . $contentInfo->id;

        if ($withFields) {
            $tags[] = 'content-fields-' . $contentInfo->id;
            $tags[] = 'content-fields-type-' . $contentInfo->contentTypeId;
        }

        if ($contentInfo->mainLocationId) {
            $tags[] = 'location-' . $contentInfo->mainLocationId;

            $location = $this->persistenceHandler->locationHandler()->load($contentInfo->mainLocationId);
            foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                $tags[] = 'location-path-' . $pathId;
            }
        }

        return $tags;
    }
}
