<?php
/**
 * File containing the Language Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Language;
use ezp\Persistence\Content\Language,
    ezp\Persistence\Content\Language\Handler as BaseLanguageHandler,
    ezp\Persistence\Content\Language\CreateStruct,
    ezp\Base\Exception;

/**
 * Language Handler
 */
class Handler implements BaseLanguageHandler
{
    /**
     * Language Gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Gateway $languageGateway
     */
    protected $languageGateway;

    /**
     * Language Mapper
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Mapper $languageMapper
     */
    protected $languageMapper;

    /**
     * Creates a new Language Handler
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Language\Gateway $languageGateway
     * @param \ezp\Persistence\Storage\Legacy\Content\Language\Mapper $languageMapper
     */
    public function __construct( Gateway $languageGateway, Mapper $languageMapper )
    {
        $this->languageGateway = $languageGateway;
        $this->languageMapper = $languageMapper;
    }

    /**
     * Create a new language
     *
     * @param \ezp\Persistence\Content\Language\CreateStruct $struct
     * @return \ezp\Persistence\Content\Language
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
     * @param \ezp\Persistence\Content\Language $language
     */
    public function update( Language $language )
    {
        $this->languageGateway->updateLanguage( $language );
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
        $rows = $this->languageGateway->loadLanguageData( $id );
        $languages = $this->languageMapper->extractLanguagesFromRows( $rows );

        if ( count( $languages ) < 1 )
        {
            throw new Exception\NotFound( 'Language', $id );
        }
        return reset( $languages );
    }

    /**
     * Get all languages
     *
     * @return \ezp\Persistence\Content\Language[]
     */
    public function loadAll()
    {
        $rows = $this->languageGateway->loadAllLanguagesData();
        return $this->languageMapper->extractLanguagesFromRows( $rows );
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
        $this->languageGateway->deleteLanguage( $id );
    }
}
?>
