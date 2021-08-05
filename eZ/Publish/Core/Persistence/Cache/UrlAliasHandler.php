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
    private const URL_ALIAS_LOCATION_TAG = 'url_alias_location';
    private const URL_ALIAS_LOCATION_PATH_TAG = 'url_alias_location_path';
    private const URL_ALIAS_NOT_FOUND_TAG = 'url_alias_not_found';
    private const URL_ALIAS_TAG = 'url_alias';
    private const PREFIXED_URL_ALIAS_LOCATION_LIST_TAG = 'prefixed_url_alias_location_list';
    private const PREFIXED_URL_ALIAS_LOCATION_LIST_CUSTOM_TAG = 'prefixed_url_alias_location_list_custom';
    private const URL_ALIAS_CUSTOM_TAG = 'url_alias_custom';
    private const PREFIXED_URL_ALIAS_URL_TAG = 'prefixed_url_alias_url';
    private const PREFIXED_URL_ALIAS_TAG = 'prefixed_url_alias';
    private const URL_ALIAS_WITH_HASH_TAG = 'url_alias_with_hash';

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
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$locationId]),
            $this->tagGenerator->generate(self::URL_ALIAS_NOT_FOUND_TAG),
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
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$locationId]),
            $this->tagGenerator->generate(self::URL_ALIAS_NOT_FOUND_TAG),
            $this->tagGenerator->generate(self::URL_ALIAS_TAG, [$urlAlias->id]),
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
            $this->tagGenerator->generate(self::URL_ALIAS_NOT_FOUND_TAG),
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
        $tagGenerator = $this->tagGenerator;

        return $this->getListCacheValue(
            ($custom) ?
                $tagGenerator->generate(self::PREFIXED_URL_ALIAS_LOCATION_LIST_CUSTOM_TAG, [$locationId]) :
                $tagGenerator->generate(self::PREFIXED_URL_ALIAS_LOCATION_LIST_TAG, [$locationId]),
            static function () use ($locationId, $custom, $persistenceHandler) {
                return $persistenceHandler->urlAliasHandler()->listURLAliasesForLocation($locationId, $custom);
            },
            static function (UrlAlias $alias) use ($persistenceHandler, $tagGenerator) {
                $tags = [
                    $tagGenerator->generate(self::URL_ALIAS_TAG, [$alias->id]),
                ];

                if ($alias->type === UrlAlias::LOCATION) {
                    $tags[] = $tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$alias->destination]);

                    $location = $persistenceHandler->locationHandler()->load($alias->destination);
                    foreach (\explode('/', trim($location->pathString, '/')) as $pathId) {
                        $tags[] = $tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$pathId]);
                    }
                }

                return $tags;
            },
            static function () { return []; },
            static function () use ($locationId, $tagGenerator) {
                return [
                    $tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
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
            $cacheTags[] = $this->tagGenerator->generate(self::URL_ALIAS_TAG, [$urlAlias->id]);
            if ($urlAlias->type === UrlAlias::LOCATION) {
                $cacheTags[] = $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$urlAlias->destination]);
                $cacheTags[] = $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$urlAlias->destination]);
            }
            if ($urlAlias->isCustom) {
                $cacheTags[] = $this->tagGenerator->generate(self::URL_ALIAS_CUSTOM_TAG, [$urlAlias->destination]);
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
            $this->tagGenerator->generate(self::PREFIXED_URL_ALIAS_URL_TAG, [$this->escapeForCacheKey($url)])
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
                    $this->tagGenerator->generate(self::URL_ALIAS_NOT_FOUND_TAG),
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
            $this->tagGenerator->generate(self::PREFIXED_URL_ALIAS_TAG, [$id])
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
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$locationId]),
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
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$newLocationId]),
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
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$locationId]),
        ];

        foreach ($childrenAliases as $childAlias) {
            $tags[] = $this->tagGenerator->generate(
                self::URL_ALIAS_WITH_HASH_TAG,
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
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$location1Id]),
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$location1Id]),
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$location2Id]),
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$location2Id]),
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
            $locationTags[] = $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]);
            $locationTags[] = $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$locationId]);
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
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
            $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$locationId]),
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
        $tags[] = $this->tagGenerator->generate(self::URL_ALIAS_TAG, [$urlAlias->id]);

        if ($urlAlias->type === UrlAlias::LOCATION) {
            $tags[] = $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$urlAlias->destination]);
            $location = $this->persistenceHandler->locationHandler()->load($urlAlias->destination);

            foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                $tags[] = $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$pathId]);
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
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_TAG, [$locationId]),
                $this->tagGenerator->generate(self::URL_ALIAS_LOCATION_PATH_TAG, [$locationId]),
            ]
        );
    }
}
