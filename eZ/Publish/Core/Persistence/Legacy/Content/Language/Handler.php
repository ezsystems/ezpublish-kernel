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
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use LogicException;

/**
 * Language Handler
 */
class Handler implements BaseLanguageHandler
{
    /**
     * Language Gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway
     */
    protected $languageGateway;

    /**
     * Language Mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper
     */
    protected $languageMapper;

    /**
     * Creates a new Language Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway $languageGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper $languageMapper
     */
    public function __construct( Gateway $languageGateway, Mapper $languageMapper )
    {
        $this->languageGateway = $languageGateway;
        $this->languageMapper = $languageMapper;
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
        $language = $this->languageMapper->createLanguageFromCreateStruct(
            $struct
        );
        $language->id = $this->languageGateway->insertLanguage( $language );
        return $language;
    }

    /**
     * Update language
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     */
    public function update( Language $language )
    {
        $this->languageGateway->updateLanguage( $language );
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
        $languages = $this->languageMapper->extractLanguagesFromRows(
            $this->languageGateway->loadLanguageData( $id )
        );

        if ( count( $languages ) < 1 )
        {
            throw new NotFoundException( 'Language', $id );
        }
        return reset( $languages );
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
        $languages = $this->languageMapper->extractLanguagesFromRows(
            $this->languageGateway->loadLanguageDataByLanguageCode( $languageCode )
        );

        if ( count( $languages ) < 1 )
        {
            throw new NotFoundException( 'Language', $languageCode );
        }
        return reset( $languages );
    }

    /**
     * Get all languages
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        return $this->languageMapper->extractLanguagesFromRows(
            $this->languageGateway->loadAllLanguagesData()
        );
    }

    /**
     * Delete a language
     *
     * @param mixed $id
     *
     * @throws LogicException If language could not be deleted
     */
    public function delete( $id )
    {
        if ( !$this->languageGateway->canDeleteLanguage( $id ) )
        {
            throw new LogicException( "Deleting language logic error, some content still references that language and therefore it can't be deleted" );
        }

        $this->languageGateway->deleteLanguage( $id );
    }
}
