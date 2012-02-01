<?php
/**
 * @package eZ\Publish\API\Interfaces
 */
namespace eZ\Publish\API\Interfaces;

use eZ\Publish\API\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Values\Content\Language;

/**
 * Language service, used for language operations
 *
 * @package eZ\Publish\API\Interfaces
 */
interface LanguageService
{
    /**
     * Creates the a new Language in the content repository
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException If user does not have access to content translations
     * @throws \eZ\Publish\API\Exceptions\IllegalArgumentException if the languageCode already exists
     *
     * @param \eZ\Publish\API\Values\Content\LanguageCreateStruct $languageCreateStruct
     *
     * @return \eZ\Publish\API\Values\Content\Language
     */
    public function createLanguage( LanguageCreateStruct $languageCreateStruct );

    /**
     * Changes the name of the language in the content repository
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Values\Content\Language $language
     * @param string $newName
     * 
     * @return \eZ\Publish\API\Values\Content\Language
     */
    public function updateLanguageName( Language $language, $newName );

    /**
     * enables a language
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Values\Content\Language $language
     */
    public function enableLanguage( $language );

    /**
     * disables a language
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Values\Content\Language $language
     */
    public function disableLanguage( $language );

    /**
     * Loads a Language from its language code ($languageCode)
     *
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if language could not be found
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Values\Content\Language
     */
    public function loadLanguage( $languageCode );

    /**
     * Loads all Languages
     *
     * @return array an aray of {@link  \eZ\Publish\API\Values\Content\Language}
     */
    public function loadLanguages();

    /**
     * Loads a Language by its id ($languageId)
     *
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if language could not be found
     *
     * @param int $languageId
     *
     * @return \eZ\Publish\API\Values\Content\Language
     */
    public function loadLanguageById( $languageId );

    /**
     * Deletes  a language from content repository
     *
     * @throws \eZ\Publish\API\Exceptions\IllegalArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param Language $language
     */
    public function deleteLanguage( $language );
    
    /**
     * returns a configured default language code
     * 
     * @return \eZ\Publish\API\Values\Content\LanguageCode
     */
    public function getDefaultLanguageCode();
}
