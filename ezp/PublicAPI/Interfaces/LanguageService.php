<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\Content\LanguageCreateStruct;
use ezp\PublicAPI\Values\Content\Language;

/**
 * Language service, used for language operations
 *
 * @package ezp\PublicAPI\Interfaces
 */
interface LanguageService
{
    /**
     * Creates the a new Language in the content repository
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If user does not have access to content translations
     * @throws \ezp\PublicAPI\Exceptions\IllegalArgumentException if the languageCode already exists
     *
     * @param \ezp\PublicAPI\Values\Content\LanguageCreateStruct $languageCreateStruct
     *
     * @return \ezp\PublicAPI\Values\Content\Language
     */
    public function createLanguage( LanguageCreateStruct $languageCreateStruct );

    /**
     * Changes the name of the language in the content repository
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \ezp\PublicAPI\Values\Content\Language $language
     * @param string $newName
     * 
     * @return \ezp\PublicAPI\Values\Content\Language
     */
    public function updateLanguageName( Language $language, $newName );

    /**
     * enables a language
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \ezp\PublicAPI\Values\Content\Language $language
     */
    public function enableLanguage( $language );

    /**
     * disables a language
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \ezp\PublicAPI\Values\Content\Language $language
     */
    public function disableLanguage( $language );

    /**
     * Loads a Language from its language code ($languageCode)
     *
     * @throws \ezp\PublicAPI\Exceptions\NotFoundException if language could not be found
     *
     * @param string $languageCode
     *
     * @return \ezp\PublicAPI\Values\Content\Language
     */
    public function loadLanguage( $languageCode );

    /**
     * Loads all Languages
     *
     * @return array an aray of {@link  \ezp\PublicAPI\Values\Content\Language}
     */
    public function loadLanguages();

    /**
     * Loads a Language by its id ($languageId)
     *
     * @throws \ezp\PublicAPI\Exceptions\NotFoundException if language could not be found
     *
     * @param int $languageId
     *
     * @return \ezp\PublicAPI\Values\Content\Language
     */
    public function loadLanguageById( $languageId );

    /**
     * Deletes  a language from content repository
     *
     * @throws \ezp\PublicAPI\Exceptions\IllegalArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param Language $language
     */
    public function deleteLanguage( $language );
    
    /**
     * returns a configured default language code
     * 
     * @return \ezp\PublicAPI\Values\Content\LanguageCode
     */
    public function getDefaultLanguageCode();
}
