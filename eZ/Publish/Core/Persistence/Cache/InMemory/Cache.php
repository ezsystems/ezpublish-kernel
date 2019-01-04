<?php

/**
 * File containing the Language InMemory Cache class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Language InMemory Cache.
 *
 * Design:
 * - Assumes all languages as cached into memory for a given TTL (Time To Live) in seconds.
 * - Also assumes initialize() is always called before retrieval, this is where TTL check happens.
 */
class Cache
{
    private const DEFAULT_CACHE_TTL = 10;

    /**
     * @var int
     */
    private $cacheTTL;

    /**
     * Maps IDs to Language objects.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    private $mapById = [];

    /**
     * Maps locales to Language objects.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    private $mapByLocale = [];

    /**
     * @var int[] Timestamp for individual cache items if set via {@link set()}.
     */
    private $idTimeStampMap = [];

    /**
     * @var int If all items have been set via {@link setAll()}, then this will hold the timestamp of that.
     */
    private $setAllTimeStamp = 0;

    /**
     * Language Cache constructor.
     *
     * @param int $cacheTTL Seconds for the cache to live..
     */
    public function __construct(int $cacheTTL = self::DEFAULT_CACHE_TTL)
    {
        $this->cacheTTL = $cacheTTL;
    }

    /**
     * Update the $language into the cache if there is a cache.
     *
     * If there is not a cache (or expired, then it does nothing.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     */
    public function set(Language $language): void
    {
        $this->mapById[$language->id] = $language;
        $this->mapByLocale[$language->languageCode] = $language;
        $this->idTimeStampMap[$language->id] = time();
    }

    /**
     * Removes the language with $id from the cache.
     *
     * @param mixed $id
     */
    public function remove(int $id): void
    {
        unset($this->mapById[$id]);
        foreach ($this->mapByLocale as $languageCode => $language) {
            if ($language->id == $id) {
                unset($this->mapByLocale[$languageCode]);
            }
        }

        unset($this->idTimeStampMap[$id]);
    }

    /**
     * Returns Languages with $ids from the cache.
     *
     * @param int[] $ids
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function getListById(array $ids): array
    {
        $languages = [];
        $time = time();
        foreach ($ids as $id) {
            if (!isset($this->mapById[$id])) {
                continue;
            }

            $language = $this->mapById[$id];
            if ($this->idTimeStampMap[$id] + $this->cacheTTL < $time) {
                continue;
            }

            $languages[$id] = $language;
        }

        return $languages;
    }

    /**
     * Returns Languages with $languageCodes from the cache.
     *
     * @param string[] $languageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function getListByLocale(array $languageCodes): array
    {
        $languages = [];
        $time = time();
        foreach ($languageCodes as $languageCode) {
            if (!isset($this->mapByLocale[$languageCode])) {
                continue;
            }

            $language = $this->mapByLocale[$languageCode];
            if ($this->idTimeStampMap[$language->id] + $this->cacheTTL < $time) {
                continue;
            }

            $languages[$languageCode] = $language;
        }

        return $languages;
    }

    /**
     * Set all languages in the cache.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language[] $languages
     */
    public function setAll(array $languages): void
    {
        $this->setAllTimeStamp = time();
        foreach ($languages as $language) {
            $this->mapById[$language->id] = $language;
            $this->mapByLocale[$language->languageCode] = $language;
            $this->idTimeStampMap[$language->id] = $this->setAllTimeStamp;
        }
    }

    /**
     * Returns all languages in the cache with locale as key.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]|null Null if cache does not have all (or expired).
     */
    public function getAll(): ?array
    {
        if ($this->setAllTimeStamp + $this->cacheTTL < time()) {
            return null;
        }

        return $this->mapByLocale;
    }

    /**
     * Clear all in-memory language cache.
     */
    public function clearCache(): void
    {
        $this->mapByLocale = $this->mapById = $this->idTimeStampMap = [];
        $this->setAllTimeStamp = 0;
    }
}
