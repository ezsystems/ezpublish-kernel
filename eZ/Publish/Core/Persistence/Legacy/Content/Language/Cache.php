<?php

/**
 * File containing the Language Cache class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Language Cache.
 */
class Cache
{
    /**
     * Maps IDs to Language objects.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    protected $mapById = [];

    /**
     * Maps locales to Language objects.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    protected $mapByLocale = [];

    /**
     * Stores the $language into the cache.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     */
    public function store(Language $language)
    {
        $this->mapById[$language->id] = $language;
        $this->mapByLocale[$language->languageCode] = $language;
    }

    /**
     * Removes the language with $id from the cache.
     *
     * @param mixed $id
     */
    public function remove($id)
    {
        unset($this->mapById[$id]);
        foreach ($this->mapByLocale as $languageCode => $language) {
            if ($language->id == $id) {
                unset($this->mapByLocale[$languageCode]);
            }
        }
    }

    /**
     * Returns the Language with $id from the cache.
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if the Language could not be found
     */
    public function getById($id)
    {
        if (!isset($this->mapById[$id])) {
            throw new NotFoundException('Language', $id);
        }

        return $this->mapById[$id];
    }

    /**
     * Returns the Language with $languageCode from the cache.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if the Language could not be found
     */
    public function getByLocale($languageCode)
    {
        if (!isset($this->mapByLocale[$languageCode])) {
            throw new NotFoundException('Language', $languageCode);
        }

        return $this->mapByLocale[$languageCode];
    }

    /**
     * Returns all languages in the cache with locale as key.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function getAll()
    {
        return $this->mapByLocale;
    }

    /**
     * CLear language cache.
     */
    public function clearCache()
    {
        $this->mapByLocale = $this->mapById = [];
    }
}
