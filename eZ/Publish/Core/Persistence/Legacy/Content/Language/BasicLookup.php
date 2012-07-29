<?php
/**
 * File containing the Language Lookup class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

class BasicLookup implements Lookup
{
    /**
     * Language handler
     *
     * @var Handler
     */
    protected $languageHandler;

    /**
     * Creates a new basic lookup using $languageHandler
     *
     * @param Handler $languageHandler
     * @return void
     */
    public function __construct( Handler $languageHandler )
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Returns the Language with $id from the cache
     *
     * @param mixed $id
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if the Language could not be found
     */
    public function getById( $id )
    {
        return $this->languageHandler->load( $id );
    }

    /**
     * Returns the Language with $languageCode from the cache
     *
     * @param string $languageCode
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *         if the Language could not be found
     */
    public function getByLocale( $languageCode )
    {
        return $this->languageHandler->loadByLanguageCode( $languageCode );
    }
}
