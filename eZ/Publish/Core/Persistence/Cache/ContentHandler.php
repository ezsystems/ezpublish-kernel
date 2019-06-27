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
class ContentHandler extends AbstractInMemoryPersistenceHandler implements ContentHandlerInterface
{
    const ALL_TRANSLATIONS_KEY = '0';

    /** @var callable */
    private $getContentInfoTags;

    /** @var callable */
    private $getContentInfoKeys;

    /** @var callable */
    private $getContentTags;

    protected function init(): void
    {
        $this->getContentInfoTags = function (ContentInfo $info, array $tags = []) {
            $tags[] = 'content-' . $info->id;

            if ($info->mainLocationId) {
                $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($info->id);
                foreach ($locations as $location) {
                    $tags[] = 'location-' . $location->id;
                    foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                        $tags[] = 'location-path-' . $pathId;
                    }
                }
            }

            return $tags;
        };
        $this->getContentInfoKeys = function (ContentInfo $info) {
            return [
                'ez-content-info-' . $info->id,
                'ez-content-info-byRemoteId-' . $this->escapeForCacheKey($info->remoteId),
            ];
        };

        $this->getContentTags = function (Content $content) {
            $versionInfo = $content->versionInfo;
            $tags = [
                'content-fields-' . $versionInfo->contentInfo->id,
                'content-fields-type-' . $versionInfo->contentInfo->contentTypeId,
            ];

            return $this->getCacheTagsForVersion($versionInfo, $tags);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        // Cached on demand when published or loaded
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);

        return $this->persistenceHandler->contentHandler()->create($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function createDraftFromVersion($contentId, $srcVersion, $userId)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $srcVersion, 'user' => $userId]);
        $draft = $this->persistenceHandler->contentHandler()->createDraftFromVersion($contentId, $srcVersion, $userId);
        $this->cache->invalidateTags(["content-{$contentId}-version-list"]);

        return $draft;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($contentId, $versionNo = null, $newOwnerId = null)
    {
        $this->logger->logCall(__METHOD__, [
            'content' => $contentId,
            'version' => $versionNo,
            'newOwner' => $newOwnerId,
        ]);

        return $this->persistenceHandler->contentHandler()->copy($contentId, $versionNo, $newOwnerId);
    }

    /**
     * {@inheritdoc}
     */
    public function load($contentId, $versionNo = null, array $translations = null)
    {
        $keySuffix = $versionNo ? "-${versionNo}-" : '-';
        $keySuffix .= empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);

        return $this->getCacheValue(
            (int) $contentId,
            'ez-content-',
            function ($id) use ($versionNo, $translations) {
                return $this->persistenceHandler->contentHandler()->load($id, $versionNo, $translations);
            },
            $this->getContentTags,
            static function (Content $content) use ($keySuffix) {
                // Version number & translations is part of keySuffix here and depends on what user asked for
                return ['ez-content-' . $content->versionInfo->contentInfo->id . $keySuffix];
            },
            $keySuffix,
            ['content' => $contentId, 'version' => $versionNo, 'translations' => $translations]
        );
    }

    public function loadContentList(array $contentIds, array $translations = null): array
    {
        $keySuffix = '-' . (empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations));

        return $this->getMultipleCacheValues(
            $contentIds,
            'ez-content-',
            function (array $cacheMissIds) use ($translations) {
                return $this->persistenceHandler->contentHandler()->loadContentList($cacheMissIds, $translations);
            },
            $this->getContentTags,
            static function (Content $content) use ($keySuffix) {
                // Version number & translations is part of keySuffix here and depends on what user asked for
                return ['ez-content-' . $content->versionInfo->contentInfo->id . $keySuffix];
            },
            $keySuffix,
            ['content' => $contentIds, 'translations' => $translations]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfo($contentId)
    {
        return $this->getCacheValue(
            $contentId,
            'ez-content-info-',
            function ($contentId) {
                return $this->persistenceHandler->contentHandler()->loadContentInfo($contentId);
            },
            $this->getContentInfoTags,
            $this->getContentInfoKeys,
            '',
            ['content' => $contentId]
        );
    }

    public function loadContentInfoList(array $contentIds)
    {
        return $this->getMultipleCacheValues(
            $contentIds,
            'ez-content-info-',
            function (array $cacheMissIds) {
                return $this->persistenceHandler->contentHandler()->loadContentInfoList($cacheMissIds);
            },
            $this->getContentInfoTags,
            $this->getContentInfoKeys,
            '',
            ['content' => $contentIds]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        return $this->getCacheValue(
            $this->escapeForCacheKey($remoteId),
            'ez-content-info-byRemoteId-',
            function () use ($remoteId) {
                return $this->persistenceHandler->contentHandler()->loadContentInfoByRemoteId($remoteId);
            },
            $this->getContentInfoTags,
            $this->getContentInfoKeys,
            '',
            ['content' => $remoteId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadVersionInfo($contentId, $versionNo)
    {
        $cacheItem = $this->cache->getItem("ez-content-version-info-${contentId}-${versionNo}");
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(['content' => $contentId, 'version' => $versionNo]);

            return $cacheItem->get();
        }

        $this->logger->logCacheMiss(['content' => $contentId, 'version' => $versionNo]);
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
        $this->logger->logCall(__METHOD__, ['user' => $userId]);

        return $this->persistenceHandler->contentHandler()->loadDraftsForUser($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($contentId, $status, $versionNo)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'status' => $status, 'version' => $versionNo]);
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
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'struct' => $struct]);
        $contentInfo = $this->persistenceHandler->contentHandler()->updateMetadata($contentId, $struct);
        $this->cache->invalidateTags(['content-' . $contentId]);

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function updateContent($contentId, $versionNo, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $versionNo, 'struct' => $struct]);
        $content = $this->persistenceHandler->contentHandler()->updateContent($contentId, $versionNo, $struct);
        $this->cache->invalidateTags(["content-{$contentId}-version-{$versionNo}"]);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContent($contentId)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId]);

        // Load reverse field relations first
        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations(
            $contentId,
            APIRelation::FIELD | APIRelation::ASSET
        );

        $return = $this->persistenceHandler->contentHandler()->deleteContent($contentId);

        if (!empty($reverseRelations)) {
            $tags = \array_map(
                static function ($relation) {
                    // only the full content object *with* fields is affected by this
                    return 'content-fields-' . $relation->sourceContentId;
                },
                $reverseRelations
            );
        } else {
            $tags = [];
        }
        $tags[] = 'content-' . $contentId;
        $this->cache->invalidateTags($tags);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteVersion($contentId, $versionNo)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $versionNo]);
        $return = $this->persistenceHandler->contentHandler()->deleteVersion($contentId, $versionNo);
        $this->cache->invalidateTags(["content-{$contentId}-version-{$versionNo}"]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function listVersions($contentId, $status = null, $limit = -1)
    {
        $cacheItem = $this->cache->getItem("ez-content-${contentId}-version-list" . ($status ? "-byStatus-${status}" : '') . "-limit-{$limit}");
        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(['content' => $contentId, 'status' => $status]);

            return $cacheItem->get();
        }

        $this->logger->logCacheMiss(['content' => $contentId, 'status' => $status]);
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
        $this->logger->logCall(__METHOD__, ['struct' => $relation]);

        return $this->persistenceHandler->contentHandler()->addRelation($relation);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRelation($relationId, $type)
    {
        $this->logger->logCall(__METHOD__, ['relation' => $relationId, 'type' => $type]);
        $this->persistenceHandler->contentHandler()->removeRelation($relationId, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRelations($sourceContentId, $sourceContentVersionNo = null, $type = null)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'content' => $sourceContentId,
                'version' => $sourceContentVersionNo,
                'type' => $type,
            ]
        );

        return $this->persistenceHandler->contentHandler()->loadRelations($sourceContentId, $sourceContentVersionNo, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReverseRelations($destinationContentId, $type = null)
    {
        $this->logger->logCall(__METHOD__, ['content' => $destinationContentId, 'type' => $type]);

        return $this->persistenceHandler->contentHandler()->loadReverseRelations($destinationContentId, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function publish($contentId, $versionNo, MetadataUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $versionNo, 'struct' => $struct]);
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
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $tags Optional, can be used to specify other tags.
     *
     * @return array
     */
    private function getCacheTagsForVersion(VersionInfo $versionInfo, array $tags = []): array
    {
        $contentInfo = $versionInfo->contentInfo;
        $tags[] = 'content-' . $contentInfo->id . '-version-' . $versionInfo->versionNo;
        $getContentInfoTagsFn = $this->getContentInfoTags;

        return $getContentInfoTagsFn($contentInfo, $tags);
    }

    private function getCacheTagsForContent(Content $content): array
    {
        $versionInfo = $content->versionInfo;
        $tags = [
            'content-fields-' . $versionInfo->contentInfo->id,
            'content-fields-type-' . $versionInfo->contentInfo->contentTypeId,
        ];

        return $this->getCacheTagsForVersion($versionInfo, $tags);
    }
}
