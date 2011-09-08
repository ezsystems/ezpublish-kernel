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
class CachingHandler implements BaseLanguageHandler
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
     * Creates a caching handler around $innerHandler
     *
     * @param \ezp\Persistence\Content\Language\Handler $innerHandler
     */
    public function __construct( BaseLanguageHandler $innerHandler, Cache $languageCache )
    {
        $this->innerHandler  = $innerHandler;
        $this->languageCache = $languageCache;

        $languages = $innerHandler->loadAll();
        foreach ( $languages as $language )
        {
            $this->languageCache->store( $language );
        }
    }

    /**
     * Create a new language
     *
     * @param \ezp\Persistence\Content\Language\CreateStruct $struct
     * @return \ezp\Persistence\Content\Language
     */
    public function create( CreateStruct $struct )
    {
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
        return $this->languageCache->getById( $id );
    }

    /**
     * Get all languages
     *
     * @return \ezp\Persistence\Content\Language[]
     */
    public function loadAll()
    {
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
        $this->innerHandler->delete( $id );
        $this->languageCache->remove( $id );
    }
}
