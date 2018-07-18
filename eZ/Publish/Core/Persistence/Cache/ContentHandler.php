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
use eZ\Publish\SPI\Persistence\Content;
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
        $draft = $this->persistenceHandler->contentHandler()->createDraftFromVersion($contentId, $srcVersion, $userId);
        $this->cache->invalidateTags(["content-{$contentId}-version-list"]);

        return $draft;
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
        $cacheItem->tag($this->getCacheTagsForContent($content));
        $this->cache->save($cacheItem);

        return $content;
    }

    public function loadContentList(array $contentLoadStructs): array
    {
        // Extract id's and make key suffix for each one (handling undefined versionNo and languages)
        $contentIds = [];
        $keySuffixes = [];
        foreach ($contentLoadStructs as $struct) {
            $contentIds[] = $struct->id;
            $keySuffixes[$struct->id] = ($struct->versionNo ? "-{$struct->versionNo}-" : '-') .
                (empty($struct->languages) ? self::ALL_TRANSLATIONS_KEY : implode('|', $struct->languages));
        }

        return $this->getMultipleCacheItems(
            $contentIds,
            'ez-content-',
            function (array $cacheMissIds) use ($contentLoadStructs) {
                $this->logger->logCall(__CLASS__ . '::loadContentList', ['content' => $cacheMissIds]);

                $filteredStructs = [];
                /* @var $contentLoadStructs \eZ\Publish\SPI\Persistence\Content\LoadStruct[] */
                foreach ($contentLoadStructs as $struct) {
                    if (in_array($struct->id, $cacheMissIds)) {
                        $filteredStructs[] = $struct;
                    }
                }

                return $this->persistenceHandler->contentHandler()->loadContentList($filteredStructs);
            },
            function (Content $content) {
                return $this->getCacheTagsForContent($content);
            },
            $keySuffixes
        );
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

    public function loadContentInfoList(array $contentIds)
    {
        return $this->getMultipleCacheItems(
            $contentIds,
            'ez-content-info-',
            function (array $cacheMissIds) {
                $this->logger->logCall(__CLASS__ . '::loadContentInfoList', ['content' => $cacheMissIds]);

                return $this->persistenceHandler->contentHandler()->loadContentInfoList($cacheMissIds);
            },
            function (ContentInfo $info) {
                return $this->getCacheTags($info);
            }
        );
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
        $cacheItem->tag($this->getCacheTagsForVersion($versionInfo));
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

        if ($status === VersionInfo::STATUS_PUBLISHED) {
            $this->cache->invalidateTags(['content-' . $contentId]);
        } else {
            $this->cache->invalidateTags(["content-{$contentId}-version-{$versionNo}"]);
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
        $this->cache->invalidateTags(["content-{$contentId}-version-{$versionNo}"]);

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
        $this->cache->invalidateTags(["content-{$contentId}-version-{$versionNo}"]);

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
        $tags = ["content-{$contentId}", "content-{$contentId}-version-list"];
        foreach ($versions as $version) {
            $tags = $this->getCacheTagsForVersion($version, $tags);
        }
        $cacheItem->tag($tags);
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
     * {@inheritdoc}
     */
    public function removeTranslationFromContent($contentId, $languageCode)
    {
        $this->deleteTranslationFromContent($contentId, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTranslationFromContent($contentId, $languageCode)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'contentId' => $contentId,
                'languageCode' => $languageCode,
            ]
        );

        $this->persistenceHandler->contentHandler()->deleteTranslationFromContent($contentId, $languageCode);
        $this->cache->invalidateTags(['content-' . $contentId]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTranslationFromDraft($contentId, $versionNo, $languageCode)
    {
        $this->logger->logCall(
            __METHOD__,
            ['content' => $contentId, 'version' => $versionNo, 'languageCode' => $languageCode]
        );
        $content = $this->persistenceHandler->contentHandler()->deleteTranslationFromDraft(
            $contentId,
            $versionNo,
            $languageCode
        );
        $this->cache->invalidateTags(["content-{$contentId}-version-{$versionNo}"]);

        return $content;
    }

    /**
     * Return relevant content and location tags so cache can be purged reliably.
     *
     * For use when generating cache, not on invalidation.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
     * @param array $tags Optional, can be used to specify other tags.
     *
     * @return array
     */
    private function getCacheTags(ContentInfo $contentInfo, array $tags = [])
    {
        $tags[] = 'content-' . $contentInfo->id;

        if ($contentInfo->mainLocationId) {
            $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentInfo->id);
            foreach ($locations as $location) {
                $tags[] = 'location-' . $location->id;
                foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                    $tags[] = 'location-path-' . $pathId;
                }
            }
        }

        return array_unique($tags);
    }

    private function getCacheTagsForVersion(VersionInfo $versionInfo, array $tags = [])
    {
        $contentInfo = $versionInfo->contentInfo;
        $tags[] = 'content-' . $contentInfo->id . '-version-' . $versionInfo->versionNo;

        return $this->getCacheTags($contentInfo, $tags);
    }

    private function getCacheTagsForContent(Content $content)
    {
        $versionInfo = $content->versionInfo;
        $tags = [
            'content-fields-' . $versionInfo->contentInfo->id,
            'content-fields-type-' . $versionInfo->contentInfo->contentTypeId,
        ];

        return $this->getCacheTagsForVersion($versionInfo, $tags);
    }
}
