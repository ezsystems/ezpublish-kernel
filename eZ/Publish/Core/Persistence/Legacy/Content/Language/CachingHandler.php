<?php
/**
 * File containing the Language Handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as BaseLanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;

/**
 * Language Handler
 */
class CachingHandler implements BaseLanguageHandler
{
    /**
     * Inner Language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $innerHandler;

    /**
     * Language cache
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected $languageCache;

    /**
     * If the cache has already been initialized
     *
     * @var boolean
     */
    protected $isCacheInitialized = false;

    /**
     * Creates a caching handler around $innerHandler
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $innerHandler
     */
    public function __construct( BaseLanguageHandler $innerHandler, Cache $languageCache )
    {
        $this->innerHandler = $innerHandler;
        $this->languageCache = $languageCache;
    }

    /**
     * Initializes the cache if necessary
     *
     * @return void
     */
    protected function initializeCache()
    {
        if ( false === $this->isCacheInitialized )
        {
            $languages = $this->innerHandler->loadAll();
            foreach ( $languages as $language )
            {
                $this->languageCache->store( $language );
            }
            $this->isCacheInitialized = true;
        }
    }

    /**
     * Create a new language
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function create( CreateStruct $struct )
    {
        $this->initializeCache();
        $language = $this->innerHandler->create( $struct );
        $this->languageCache->store( $language );
        return $language;
    }

    /**
     * Update language
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     */
    public function update( Language $language )
    {
        $this->initializeCache();
        $this->innerHandler->update( $language );
        $this->languageCache->store( $language );
    }

    /**
     * Get language by id
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function load( $id )
    {
        $this->initializeCache();
        return $this->languageCache->getById( $id );
    }

    /**
     * Get language by Language Code (eg: eng-GB)
     *
     * @param string $languageCode
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function loadByLanguageCode( $languageCode )
    {
        $this->initializeCache();
        return $this->languageCache->getByLocale( $languageCode );
    }

    /**
     * Get all languages
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        $this->initializeCache();
        return $this->languageCache->getAll();
    }

    /**
     * Delete a language
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $this->initializeCache();
        $this->innerHandler->delete( $id );
        $this->languageCache->remove( $id );
    }

    /**
     * Clear internal cache
     *
     * @return void
     */
    public function clearCache()
    {
        $this->isCacheInitialized = false;
        $this->languageCache->clearCache();
    }
}
