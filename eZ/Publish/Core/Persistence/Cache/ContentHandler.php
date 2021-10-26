<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Handler
 */
class ContentHandler extends AbstractInMemoryPersistenceHandler implements ContentHandlerInterface
{
    private const CONTENT_IDENTIFIER = 'content';
    private const LOCATION_IDENTIFIER = 'location';
    private const LOCATION_PATH_IDENTIFIER = 'location_path';
    private const CONTENT_INFO_IDENTIFIER = 'content_info';
    private const CONTENT_INFO_BY_REMOTE_ID_IDENTIFIER = 'content_info_by_remote_id';
    private const CONTENT_FIELDS_TYPE_IDENTIFIER = 'content_fields_type';
    private const CONTENT_VERSION_LIST_IDENTIFIER = 'content_version_list';
    private const CONTENT_VERSION_INFO_IDENTIFIER = 'content_version_info';
    private const CONTENT_VERSION_IDENTIFIER = 'content_version';

    public const ALL_TRANSLATIONS_KEY = '0';

    /** @var callable */
    private $getContentInfoTags;

    /** @var callable */
    private $getContentInfoKeys;

    /** @var callable */
    private $getContentTags;

    protected function init(): void
    {
        $this->getContentInfoTags = function (ContentInfo $info, array $tags = []) {
            $tags[] = $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$info->id]);

            if ($info->mainLocationId) {
                $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($info->id);
                foreach ($locations as $location) {
                    $tags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_IDENTIFIER, [$location->id]);
                    $pathIds = $this->locationPathConverter->convertToPathIds($location->pathString);
                    foreach ($pathIds as $pathId) {
                        $tags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$pathId]);
                    }
                }
            }

            return $tags;
        };
        $this->getContentInfoKeys = function (ContentInfo $info) {
            return [
                $this->cacheIdentifierGenerator->generateKey(self::CONTENT_INFO_IDENTIFIER, [$info->id], true),
                $this->cacheIdentifierGenerator->generateKey(
                    self::CONTENT_INFO_BY_REMOTE_ID_IDENTIFIER,
                    [$this->cacheIdentifierSanitizer->escapeForCacheKey($info->remoteId)],
                    true
                ),
            ];
        };

        $this->getContentTags = function (Content $content) {
            $versionInfo = $content->versionInfo;
            $tags = [
                $this->cacheIdentifierGenerator->generateTag(
                    self::CONTENT_FIELDS_TYPE_IDENTIFIER,
                    [$versionInfo->contentInfo->contentTypeId]
                ),
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
    public function createDraftFromVersion($contentId, $srcVersion, $userId, ?string $languageCode = null)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $srcVersion, 'user' => $userId]);
        $draft = $this->persistenceHandler->contentHandler()->createDraftFromVersion($contentId, $srcVersion, $userId, $languageCode);
        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_VERSION_LIST_IDENTIFIER, [$contentId], true),
        ]);

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
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_IDENTIFIER, [], true) . '-',
            function ($id) use ($versionNo, $translations) {
                return $this->persistenceHandler->contentHandler()->load($id, $versionNo, $translations);
            },
            $this->getContentTags,
            function (Content $content) use ($keySuffix) {
                // Version number & translations is part of keySuffix here and depends on what user asked for
                return [
                    $this->cacheIdentifierGenerator->generateKey(
                        self::CONTENT_IDENTIFIER,
                        [$content->versionInfo->contentInfo->id],
                        true
                    ) . $keySuffix,
                ];
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
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_IDENTIFIER, [], true) . '-',
            function (array $cacheMissIds) use ($translations) {
                return $this->persistenceHandler->contentHandler()->loadContentList($cacheMissIds, $translations);
            },
            $this->getContentTags,
            function (Content $content) use ($keySuffix) {
                // Translations is part of keySuffix here and depends on what user asked for
                return [
                    $this->cacheIdentifierGenerator->generateKey(
                        self::CONTENT_IDENTIFIER,
                        [$content->versionInfo->contentInfo->id],
                        true
                    ) . $keySuffix,
                ];
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
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_INFO_IDENTIFIER, [], true) . '-',
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
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_INFO_IDENTIFIER, [], true) . '-',
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
            $this->cacheIdentifierSanitizer->escapeForCacheKey($remoteId),
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_INFO_BY_REMOTE_ID_IDENTIFIER, [], true) . '-',
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
    public function loadVersionInfo($contentId, $versionNo = null)
    {
        $keySuffix = $versionNo ? "-${versionNo}" : '';
        $cacheItem = $this->cache->getItem(
            $this->cacheIdentifierGenerator->generateKey(
                self::CONTENT_VERSION_INFO_IDENTIFIER,
                [$contentId],
                true
            ) . $keySuffix
        );

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
    public function countDraftsForUser(int $userId): int
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId]);

        return $this->persistenceHandler->contentHandler()->countDraftsForUser($userId);
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
    public function loadDraftListForUser(int $userId, int $offset = 0, int $limit = -1): array
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId, 'offset' => $offset, 'limit' => $limit]);

        return $this->persistenceHandler->contentHandler()->loadDraftListForUser($userId, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($contentId, $status, $versionNo)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'status' => $status, 'version' => $versionNo]);
        $return = $this->persistenceHandler->contentHandler()->setStatus($contentId, $status, $versionNo);

        if ($status === VersionInfo::STATUS_PUBLISHED) {
            $this->cache->invalidateTags([
                $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
            ]);
        } else {
            $this->cache->invalidateTags([
                $this->cacheIdentifierGenerator->generateTag(
                    self::CONTENT_VERSION_IDENTIFIER,
                    [$contentId, $versionNo]
                ),
            ]);
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
        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
        ]);

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function updateContent($contentId, $versionNo, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $versionNo, 'struct' => $struct]);
        $content = $this->persistenceHandler->contentHandler()->updateContent($contentId, $versionNo, $struct);

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(
                self::CONTENT_VERSION_IDENTIFIER,
                [$contentId, $versionNo]
            ),
        ]);

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
                function ($relation) {
                    // only the full content object *with* fields is affected by this
                    return $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$relation->sourceContentId]);
                },
                $reverseRelations
            );
        } else {
            $tags = [];
        }
        $tags[] = $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]);
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

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(
                self::CONTENT_VERSION_IDENTIFIER,
                [$contentId, $versionNo]
            ),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function listVersions($contentId, $status = null, $limit = -1)
    {
        // Don't cache non typical lookups to avoid filling up cache and tags.
        if ($status !== null || $limit !== -1) {
            $this->logger->logCall(__METHOD__, ['content' => $contentId, 'status' => $status]);

            return $this->persistenceHandler->contentHandler()->listVersions($contentId, $status, $limit);
        }

        // Cache default lookups
        $cacheItem = $this->cache->getItem(
            $this->cacheIdentifierGenerator->generateKey(self::CONTENT_VERSION_LIST_IDENTIFIER, [$contentId], true)
        );

        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(['content' => $contentId]);

            return $cacheItem->get();
        }

        $this->logger->logCacheMiss(['content' => $contentId]);
        $versions = $this->persistenceHandler->contentHandler()->listVersions($contentId, $status, $limit);
        $cacheItem->set($versions);
        $tags = [
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
        ];

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
    public function countReverseRelations(int $destinationContentId, ?int $type = null): int
    {
        $this->logger->logCall(__METHOD__, ['content' => $destinationContentId, 'type' => $type]);

        return $this->persistenceHandler->contentHandler()->countReverseRelations($destinationContentId, $type);
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
    public function loadReverseRelationList(
        int $destinationContentId,
        int $offset = 0,
        int $limit = -1,
        ?int $type = null
    ): array {
        $this->logger->logCall(__METHOD__, [
            'content' => $destinationContentId,
            'offset' => $offset,
            'limit' => $limit,
            'type' => $type,
        ]);

        return $this->persistenceHandler->contentHandler()->loadReverseRelationList(
            $destinationContentId,
            $offset,
            $limit,
            $type
        );
    }

    /**
     * {@inheritdoc}
     */
    public function publish($contentId, $versionNo, MetadataUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['content' => $contentId, 'version' => $versionNo, 'struct' => $struct]);
        $content = $this->persistenceHandler->contentHandler()->publish($contentId, $versionNo, $struct);
        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
        ]);

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
        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$contentId]),
        ]);
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

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_VERSION_IDENTIFIER, [$contentId, $versionNo]),
        ]);

        return $content;
    }

    /**
     * Return relevant content and location tags so cache can be purged reliably.
     *
     * For use when generating cache, not on invalidation.
     *
     * @param array $tags Optional, can be used to specify other tags.
     */
    private function getCacheTagsForVersion(VersionInfo $versionInfo, array $tags = []): array
    {
        $contentInfo = $versionInfo->contentInfo;
        $tags[] = $this->cacheIdentifierGenerator->generateTag(
            self::CONTENT_VERSION_IDENTIFIER,
            [$contentInfo->id, $versionInfo->versionNo]
        );

        $getContentInfoTagsFn = $this->getContentInfoTags;

        return $getContentInfoTagsFn($contentInfo, $tags);
    }
}
