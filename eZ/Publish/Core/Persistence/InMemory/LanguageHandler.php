<?php
/**
 * File containing the LanguageHandler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\Language,
    eZ\Publish\SPI\Persistence\Content\Language\CreateStruct,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

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
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $id
     */
    public function load( $id )
    {
        return $this->backend->load( 'Content\\Language', $id );
    }

    /**
     * Get language by Language Code (eg: eng-GB)
     *
     * @param string $languageCode
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $languageCode
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
     * @todo Might throw an exception if the language is still associated with some content / types / (...) ?
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $this->backend->delete( 'Content\\Language', $id );
    }
}
