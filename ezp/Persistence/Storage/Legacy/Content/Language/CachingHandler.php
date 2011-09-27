<?php
/**
 * File containing the Language Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Language;
use ezp\Persistence\Content\Language,
    ezp\Persistence\Content\Language\Handler as BaseLanguageHandler,
    ezp\Persistence\Content\Language\CreateStruct;

/**
 * Language Handler
 */
class CachingHandler implements BaseLanguageHandler, Lookup
{
    /**
     * Inner Language handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Handler
     */
    protected $innerHandler;

    /**
     * Language cache
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Cache
     */
    protected $languageCache;

    /**
     * If the cache has already been initialized
     *
     * @var bool
     */
    protected $isCacheInitialized = false;

    /**
     * Creates a caching handler around $innerHandler
     *
     * @param \ezp\Persistence\Content\Language\Handler $innerHandler
     */
    public function __construct( BaseLanguageHandler $innerHandler, Cache $languageCache )
    {
        $this->innerHandler  = $innerHandler;
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
     * Returns the Language with $id from the cache
     *
     * @param mixed $id
     * @return \ezp\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound
     *         if the Language could not be found
     */
    public function getById( $id )
    {
        $this->initializeCache();
        return $this->languageCache->getById( $id );
    }

    /**
     * Returns the Language with $locale from the cache
     *
     * @param string $locale
     * @return \ezp\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound
     *         if the Language could not be found
     */
    public function getByLocale( $locale )
    {
        $this->initializeCache();
        return $this->languageCache->getByLocale( $locale );
    }

    /**
     * Create a new language
     *
     * @param \ezp\Persistence\Content\Language\CreateStruct $struct
     * @return \ezp\Persistence\Content\Language
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
     * @param \ezp\Persistence\Content\Language $language
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
     * @return \ezp\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound If language could not be found by $id
     */
    public function load( $id )
    {
        $this->initializeCache();
        return $this->languageCache->getById( $id );
    }

    /**
     * Get all languages
     *
     * @return \ezp\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        $this->initializeCache();
        return $this->languageCache->getAll();
    }

    /**
     * Delete a language
     *
     * @todo Might throw an exception if the language is still associated with
     *       some content / types / (...) ?
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $this->initializeCache();
        $this->innerHandler->delete( $id );
        $this->languageCache->remove( $id );
    }
}
