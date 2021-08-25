<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\UrlAlias;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
 */
class UrlAliasHandler extends AbstractInMemoryPersistenceHandler implements UrlAliasHandlerInterface
{
    private const URL_ALIAS_LOCATION_IDENTIFIER = 'url_alias_location';
    private const URL_ALIAS_LOCATION_PATH_IDENTIFIER = 'url_alias_location_path';
    private const URL_ALIAS_NOT_FOUND_IDENTIFIER = 'url_alias_not_found';
    private const URL_ALIAS_IDENTIFIER = 'url_alias';
    private const URL_ALIAS_LOCATION_LIST_IDENTIFIER = 'url_alias_location_list';
    private const URL_ALIAS_LOCATION_LIST_CUSTOM_IDENTIFIER = 'url_alias_location_list_custom';
    private const URL_ALIAS_CUSTOM_IDENTIFIER = 'url_alias_custom';
    private const URL_ALIAS_URL_IDENTIFIER = 'url_alias_url';
    private const URL_ALIAS_WITH_HASH_IDENTIFIER = 'url_alias_with_hash';

    /**
     * Constant used for storing not found results for lookup().
     */
    const NOT_FOUND = 0;

    /**
     * {@inheritdoc}
     */
    public function publishUrlAliasForLocation(
        $locationId,
        $parentLocationId,
        $name,
        $languageCode,
        $alwaysAvailable = false,
        $updatePathIdentificationString = false
    ) {
        $this->logger->logCall(
            __METHOD__,
            [
                'location' => $locationId,
                'parent' => $parentLocationId,
                'name' => $name,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable,
            ]
        );

        $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
            $locationId,
            $parentLocationId,
            $name,
            $languageCode,
            $alwaysAvailable,
            $updatePathIdentificationString
        );

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$locationId]),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_NOT_FOUND_IDENTIFIER),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomUrlAlias($locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'location' => $locationId,
                '$path' => $path,
                '$forwarding' => $forwarding,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable,
            ]
        );

        $urlAlias = $this->persistenceHandler->urlAliasHandler()->createCustomUrlAlias(
            $locationId,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$locationId]),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_NOT_FOUND_IDENTIFIER),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_IDENTIFIER, [$urlAlias->id]),
        ]);

        return $urlAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function createGlobalUrlAlias($resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'resource' => $resource,
                'path' => $path,
                'forwarding' => $forwarding,
                'language' => $languageCode,
                'alwaysAvailable' => $alwaysAvailable,
            ]
        );

        $urlAlias = $this->persistenceHandler->urlAliasHandler()->createGlobalUrlAlias(
            $resource,
            $path,
            $forwarding,
            $languageCode,
            $alwaysAvailable
        );

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_NOT_FOUND_IDENTIFIER),
        ]);

        return $urlAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function listGlobalURLAliases($languageCode = null, $offset = 0, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, ['language' => $languageCode, 'offset' => $offset, 'limit' => $limit]);

        return $this->persistenceHandler->urlAliasHandler()->listGlobalURLAliases($languageCode, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function listURLAliasesForLocation($locationId, $custom = false)
    {
        $persistenceHandler = $this->persistenceHandler;
        $cacheIdentifierGenerator = $this->cacheIdentifierGenerator;

        return $this->getListCacheValue(
            ($custom) ?
                $cacheIdentifierGenerator->generateKey(self::URL_ALIAS_LOCATION_LIST_CUSTOM_IDENTIFIER, [$locationId], true) :
                $cacheIdentifierGenerator->generateKey(self::URL_ALIAS_LOCATION_LIST_IDENTIFIER, [$locationId], true),
            static function () use ($locationId, $custom, $persistenceHandler) {
                return $persistenceHandler->urlAliasHandler()->listURLAliasesForLocation($locationId, $custom);
            },
            static function (UrlAlias $alias) use ($persistenceHandler, $cacheIdentifierGenerator) {
                $tags = [
                    $cacheIdentifierGenerator->generateTag(self::URL_ALIAS_IDENTIFIER, [$alias->id]),
                ];

                if ($alias->type === UrlAlias::LOCATION) {
                    $tags[] = $cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$alias->destination]);

                    $location = $persistenceHandler->locationHandler()->load($alias->destination);
                    foreach (\explode('/', trim($location->pathString, '/')) as $pathId) {
                        $tags[] = $cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$pathId]);
                    }
                }

                return $tags;
            },
            static function () { return []; },
            static function () use ($locationId, $cacheIdentifierGenerator) {
                return [
                    $cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
                ];
            },
            ['location' => $locationId, 'custom' => $custom]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeURLAliases(array $urlAliases)
    {
        $this->logger->logCall(__METHOD__, ['aliases' => $urlAliases]);
        $return = $this->persistenceHandler->urlAliasHandler()->removeURLAliases($urlAliases);

        $cacheTags = [];
        foreach ($urlAliases as $urlAlias) {
            $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_IDENTIFIER, [$urlAlias->id]);
            if ($urlAlias->type === UrlAlias::LOCATION) {
                $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$urlAlias->destination]);
                $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$urlAlias->destination]);
            }
            if ($urlAlias->isCustom) {
                $cacheTags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_CUSTOM_IDENTIFIER, [$urlAlias->destination]);
            }
        }
        $this->cache->invalidateTags($cacheTags);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup($url)
    {
        $cacheItem = $this->cache->getItem(
            $this->cacheIdentifierGenerator->generateKey(
                self::URL_ALIAS_URL_IDENTIFIER,
                [$this->escapeForCacheKey($url)],
                true
            )
        );

        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(['url' => $url]);

            if (($return = $cacheItem->get()) === self::NOT_FOUND) {
                throw new NotFoundException('UrlAlias', $url);
            }

            return $return;
        }

        $this->logger->logCacheMiss(['url' => $url]);
        try {
            $urlAlias = $this->persistenceHandler->urlAliasHandler()->lookup($url);
        } catch (APINotFoundException $e) {
            $cacheItem->set(self::NOT_FOUND)
                ->expiresAfter(30)
                ->tag([
                    $this->cacheIdentifierGenerator->generateKey(self::URL_ALIAS_NOT_FOUND_IDENTIFIER),
                ]);
            $this->cache->save($cacheItem);
            throw $e;
        }

        $cacheItem->set($urlAlias);
        $cacheItem->tag($this->getCacheTags($urlAlias));
        $this->cache->save($cacheItem);

        return $urlAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUrlAlias($id)
    {
        $cacheItem = $this->cache->getItem(
            $this->cacheIdentifierGenerator->generateKey(self::URL_ALIAS_IDENTIFIER, [$id], true)
        );

        if ($cacheItem->isHit()) {
            $this->logger->logCacheHit(['alias' => $id]);

            return $cacheItem->get();
        }

        $this->logger->logCacheMiss(['alias' => $id]);
        $urlAlias = $this->persistenceHandler->urlAliasHandler()->loadUrlAlias($id);

        $cacheItem->set($urlAlias);
        $cacheItem->tag($this->getCacheTags($urlAlias));
        $this->cache->save($cacheItem);

        return $urlAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function locationMoved($locationId, $oldParentId, $newParentId)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'location' => $locationId,
                'oldParent' => $oldParentId,
                'newParent' => $newParentId,
            ]
        );

        $return = $this->persistenceHandler->urlAliasHandler()->locationMoved($locationId, $oldParentId, $newParentId);

        if ($oldParentId !== $newParentId) {
            $this->cache->invalidateTags([
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$locationId]),
            ]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function locationCopied($locationId, $newLocationId, $newParentId)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'oldLocation' => $locationId,
                'newLocation' => $newLocationId,
                'newParent' => $newParentId,
            ]
        );

        $return = $this->persistenceHandler->urlAliasHandler()->locationCopied(
            $locationId,
            $newLocationId,
            $newParentId
        );
        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$newLocationId]),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function locationDeleted($locationId): array
    {
        $this->logger->logCall(__METHOD__, ['location' => $locationId]);
        $childrenAliases = $this->persistenceHandler->urlAliasHandler()
            ->locationDeleted($locationId);

        $tags = [
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$locationId]),
        ];

        foreach ($childrenAliases as $childAlias) {
            $tags[] = $this->cacheIdentifierGenerator->generateTag(
                self::URL_ALIAS_WITH_HASH_IDENTIFIER,
                [$locationId, $childAlias['text_md5']]
            );
        }

        $this->cache->invalidateTags($tags);

        return $childrenAliases;
    }

    /**
     * {@inheritdoc}
     */
    public function locationSwapped($location1Id, $location1ParentId, $location2Id, $location2ParentId)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'location1Id' => $location1Id,
                'location1ParentId' => $location1ParentId,
                'location2Id' => $location2Id,
                'location2ParentId' => $location2ParentId,
            ]
        );

        $return = $this->persistenceHandler->urlAliasHandler()->locationSwapped(
            $location1Id,
            $location1ParentId,
            $location2Id,
            $location2ParentId
        );

        $this->cache->invalidateTags(
            [
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$location1Id]),
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$location1Id]),
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$location2Id]),
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$location2Id]),
            ]
        );

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function translationRemoved(array $locationIds, $languageCode)
    {
        $this->logger->logCall(
            __METHOD__,
            ['locations' => implode(',', $locationIds), 'language' => $languageCode]
        );

        $this->persistenceHandler->urlAliasHandler()->translationRemoved($locationIds, $languageCode);

        $locationTags = [];
        foreach ($locationIds as $locationId) {
            $locationTags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]);
            $locationTags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$locationId]);
        }

        $this->cache->invalidateTags($locationTags);
    }

    /**
     * {@inheritdoc}
     */
    public function archiveUrlAliasesForDeletedTranslations($locationId, $parentLocationId, array $languageCodes)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'locationId' => $locationId,
                'parentLocationId' => $parentLocationId,
                'languageCodes' => implode(',', $languageCodes),
            ]
        );

        $this->persistenceHandler->urlAliasHandler()->archiveUrlAliasesForDeletedTranslations(
            $locationId,
            $parentLocationId,
            $languageCodes
        );

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
            $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$locationId]),
        ]);
    }

    /**
     * Return relevant UrlAlias and optionally UrlAlias location tags so cache can be purged reliably.
     *
     * For use when generating cache, not on invalidation.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlAlias $urlAlias
     * @param array $tags Optional, can be used to specify other tags.
     *
     * @return array
     */
    private function getCacheTags(UrlAlias $urlAlias, array $tags = [])
    {
        $tags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_IDENTIFIER, [$urlAlias->id]);

        if ($urlAlias->type === UrlAlias::LOCATION) {
            $tags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$urlAlias->destination]);
            $location = $this->persistenceHandler->locationHandler()->load($urlAlias->destination);

            foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                $tags[] = $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$pathId]);
            }
        }

        return array_unique($tags);
    }

    /**
     * Delete corrupted URL aliases (global, custom and system).
     *
     * @return int Number of deleted URL aliases
     */
    public function deleteCorruptedUrlAliases()
    {
        $this->logger->logCall(__METHOD__);

        $deletedCount = $this->persistenceHandler->urlAliasHandler()->deleteCorruptedUrlAliases();

        if ($deletedCount) {
            $this->cache->clear();//!TIMBER!: Deletes all cache
        }

        return $deletedCount;
    }

    /**
     * Attempt repairing auto-generated URL aliases for the given Location (including history).
     *
     * Note: it is assumed that at this point original, working, URL Alias for Location is published.
     *
     * @param int $locationId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function repairBrokenUrlAliasesForLocation(int $locationId)
    {
        $this->logger->logCall(__METHOD__, ['locationId' => $locationId]);

        $this->persistenceHandler->urlAliasHandler()->repairBrokenUrlAliasesForLocation($locationId);

        $this->cache->invalidateTags(
            [
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_IDENTIFIER, [$locationId]),
                $this->cacheIdentifierGenerator->generateTag(self::URL_ALIAS_LOCATION_PATH_IDENTIFIER, [$locationId]),
            ]
        );
    }
}
