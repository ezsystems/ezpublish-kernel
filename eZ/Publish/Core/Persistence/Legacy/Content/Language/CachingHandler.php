<?php

/**
 * File containing the Language Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as BaseLanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * Language Handler.
 */
class CachingHandler implements BaseLanguageHandler
{
    /**
     * Inner Language handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $innerHandler;

    /**
     * Language cache.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected $inMemoryCache;

    /**
     * Creates a caching handler around $innerHandler.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $innerHandler
     */
    public function __construct(BaseLanguageHandler $innerHandler, Cache $languageCache)
    {
        $this->innerHandler = $innerHandler;
        $this->inMemoryCache = $languageCache;
    }

    /**
     * Initializes the cache if necessary.
     */
    private function initializeCache()
    {
        $this->inMemoryCache->initialize(function() {
            return $this->innerHandler->loadAll();
        });
    }

    /**
     * Create a new language.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function create(CreateStruct $struct)
    {
        $language = $this->innerHandler->create($struct);
        $this->inMemoryCache->updateIfInitialized($language);

        return $language;
    }

    /**
     * Update language.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     */
    public function update(Language $language)
    {
        $this->innerHandler->update($language);
        $this->inMemoryCache->updateIfInitialized($language);
    }

    /**
     * Get language by id.
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function load($id)
    {
        $this->initializeCache();

        return $this->inMemoryCache->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadList(array $ids): iterable
    {
        $this->initializeCache();

        return $this->inMemoryCache->getListById($ids);
    }

    /**
     * Get language by Language Code (eg: eng-GB).
     *
     * @param string $languageCode
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function loadByLanguageCode($languageCode)
    {
        $this->initializeCache();

        return $this->inMemoryCache->getByLocale($languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function loadListByLanguageCodes(array $languageCodes): iterable
    {
        $this->initializeCache();

        return $this->inMemoryCache->getListByLocale($languageCodes);
    }

    /**
     * Get all languages.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        $this->initializeCache();

        return $this->inMemoryCache->getAll();
    }

    /**
     * Delete a language.
     *
     * @param mixed $id
     */
    public function delete($id)
    {
        $this->innerHandler->delete($id);
        $this->inMemoryCache->remove($id);
    }

    /**
     * Clear internal cache.
     */
    public function clearCache()
    {
        $this->inMemoryCache->clearCache();
    }
}
