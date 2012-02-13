<?php
/**
 * File containing the LanguageServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\Repository;
use \eZ\Publish\API\Repository\LanguageService;
use \eZ\Publish\API\Repository\Values\Content\Language;
use \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\IllegalArgumentExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\LanguageService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\LanguageService
 */
class LanguageServiceStub implements LanguageService
{
    /**
     * @var integer
     */
    private $nextId = 0;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    private $languages = array();

    /**
     * @var array
     */
    private $codes = array();

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * Instantiates the language service.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( Repository $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Creates the a new Language in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the languageCode already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct $languageCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function createLanguage( LanguageCreateStruct $languageCreateStruct )
    {
        if ( isset( $this->codes[$languageCreateStruct->languageCode] ) )
        {
            throw new IllegalArgumentExceptionStub( '@TODO: What error code should be used?' );
        }

        $language = new Language(
            array(
                'id'            =>  ++$this->nextId,
                'name'          =>  $languageCreateStruct->name,
                'enabled'       =>  $languageCreateStruct->enabled,
                'languageCode'  =>  $languageCreateStruct->languageCode
            )
        );

        $this->languages[$language->id]       = $language;
        $this->codes[$language->languageCode] = $language->id;

        return $language;
    }

    /**
     * Changes the name of the language in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     * @param string $newName
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function updateLanguageName( Language $language, $newName )
    {
        $this->languages[$language->id] = new Language(
            array(
                'id'            =>  $language->id,
                'name'          =>  $newName,
                'enabled'       =>  $language->enabled,
                'languageCode'  =>  $language->languageCode
            )
        );

        return $this->languages[$language->id];
    }

    /**
     * enables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function enableLanguage( Language $language )
    {
        $this->languages[$language->id] = new Language(
            array(
                'id'            =>  $language->id,
                'name'          =>  $language->name,
                'enabled'       =>  true,
                'languageCode'  =>  $language->languageCode
            )
        );
    }

    /**
     * disables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function disableLanguage( Language $language )
    {
        $this->languages[$language->id] = new Language(
            array(
                'id'            =>  $language->id,
                'name'          =>  $language->name,
                'enabled'       =>  false,
                'languageCode'  =>  $language->languageCode
            )
        );
    }

    /**
     * Loads a Language from its language code ($languageCode)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguage( $languageCode )
    {
        if ( isset( $this->codes[$languageCode] ) )
        {
            return $this->languages[$this->codes[$languageCode]];
        }
        throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
    }

    /**
     * Loads all Languages
     *
     * @return array an aray of {@link  \eZ\Publish\API\Repository\Values\Content\Language}
     */
    public function loadLanguages()
    {
        return array_values( $this->languages );
    }

    /**
     * Loads a Language by its id ($languageId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param int $languageId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguageById( $languageId )
    {
        if ( isset( $this->languages[$languageId] ) )
        {
            return $this->languages[$languageId];
        }
        throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
    }

    /**
     * Deletes  a language from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function deleteLanguage( Language $language )
    {
        unset( $this->languages[$language->id], $this->codes[$language->languageCode] );
    }

    /**
     * returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        // TODO: Implement getDefaultLanguageCode() method.
    }

}
