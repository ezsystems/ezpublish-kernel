<?php
/**
 * File containing the LanguageHandler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use LogicException;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Section\Handler
 */
class LanguageHandler implements LanguageHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
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
        return $this->backend->create( 'Content\\Language', (array)$struct );
    }

    /**
     * Update language
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $struct
     */
    public function update( Language $struct )
    {
        $this->backend->update(
            'Content\\Language',
            $struct->id,
            (array)$struct
        );
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
        return $this->backend->load( 'Content\\Language', $id );
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
        $languages = $this->backend->find( 'Content\\Language', array( 'languageCode' => $languageCode ) );
        if ( empty( $languages ) )
            throw new NotFound( 'Content\\Language', $languageCode );

        return $languages[0];
    }

    /**
     * Get all languages
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        $languages = array();
        foreach ( $this->backend->find( 'Content\\Language', array() ) as  $language )
        {
            $languages[$language->languageCode] = $language;
        }
        return $languages;
    }

    /**
     * Delete a language
     *
     * @throws \LogicException If language could not be deleted
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $versions = $this->backend->find( 'Content\\VersionInfo', array( 'languageIds' => $id ) );
        if ( !empty( $versions ) )
        {
            throw new LogicException( "Deleting language logic error, some content still references that language and therefore it can't be deleted" );
        }

        $this->backend->delete( 'Content\\Language', $id );
    }
}
