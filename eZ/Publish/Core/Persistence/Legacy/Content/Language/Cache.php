<?php
/**
 * File containing the Language Cache class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language,
    ezp\Base\Exception;

/**
 * Language Cache
 */
class Cache
{
    /**
     * Maps IDs to Language objects
     *
     * @var \ezp\Content\Language[]
     */
    protected $mapById = array();

    /**
     * Maps locales to Language objects
     *
     * @var \ezp\Content\Language[]
     */
    protected $mapByLocale = array();

    /**
     * Stores the $language into the cache
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     * @return void
     */
    public function store( Language $language )
    {
        $this->mapById[$language->id] = $language;
        $this->mapByLocale[$language->locale] = $language;
    }

    /**
     * Removes the language with $id from the cache
     *
     * @param mixed $id
     * @return void
     */
    public function remove( $id )
    {
        unset( $this->mapById[$id] );
        foreach ( $this->mapByLocale as $locale => $language )
        {
            if ( $language->id == $id )
            {
                unset( $this->mapByLocale[$locale] );
            }
        }
    }

    /**
     * Returns the Language with $id from the cache
     *
     * @param mixed $id
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound
     *         if the Language could not be found
     */
    public function getById( $id )
    {
        if ( !isset( $this->mapById[$id] ) )
        {
            throw new Exception\NotFound( 'Language', $id );
        }
        return $this->mapById[$id];
    }

    /**
     * Returns the Language with $locale from the cache
     *
     * @param string $locale
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     * @throws \ezp\Base\Exception\NotFound
     *         if the Language could not be found
     */
    public function getByLocale( $locale )
    {
        if ( !isset( $this->mapByLocale[$locale] ) )
        {
            throw new Exception\NotFound( 'Language', $locale );
        }
        return $this->mapByLocale[$locale];
    }

    /**
     * Returns all languages in the cache
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function getAll()
    {
        return array_values( $this->mapById );
    }
}
