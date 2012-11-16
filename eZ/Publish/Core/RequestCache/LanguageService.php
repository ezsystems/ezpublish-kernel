<?php
/**
 * LanguageService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\RequestCache;

use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\RequestCache\Signal\LanguageService\CreateLanguageSignal;
use eZ\Publish\Core\RequestCache\Signal\LanguageService\UpdateLanguageNameSignal;
use eZ\Publish\Core\RequestCache\Signal\LanguageService\EnableLanguageSignal;
use eZ\Publish\Core\RequestCache\Signal\LanguageService\DisableLanguageSignal;
use eZ\Publish\Core\RequestCache\Signal\LanguageService\DeleteLanguageSignal;

/**
 * LanguageService class
 * @package eZ\Publish\Core\RequestCache
 */
class LanguageService implements LanguageServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $service;

    /**
     * CachePool
     *
     * @var \eZ\Publish\Core\RequestCache\CachePool
     */
    protected $cachePool;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\LanguageService $service
     * @param \eZ\Publish\Core\RequestCache\CachePool $cachePool
     */
    public function __construct( LanguageServiceInterface $service, CachePool $cachePool )
    {
        $this->service          = $service;
        $this->cachePool = $cachePool;
    }

    /**
     * Creates the a new Language in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the languageCode already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct $languageCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function createLanguage( LanguageCreateStruct $languageCreateStruct )
    {
        return $this->service->createLanguage( $languageCreateStruct );
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
        return $this->service->updateLanguageName( $language, $newName );
    }

    /**
     * enables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function enableLanguage( Language $language )
    {
        return $this->service->enableLanguage( $language );
    }

    /**
     * disables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function disableLanguage( Language $language )
    {
        return $this->service->disableLanguage( $language );
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
        return $this->service->loadLanguage( $languageCode );
    }

    /**
     * Loads all Languages
     *
     * @return array an array of {@link  \eZ\Publish\API\Repository\Values\Content\Language}
     */
    public function loadLanguages()
    {
        return $this->service->loadLanguages();
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
        return $this->service->loadLanguageById( $languageId );
    }

    /**
     * Deletes  a language from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user is not allowed to delete a language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function deleteLanguage( Language $language )
    {
        return $this->service->deleteLanguage( $language );
    }

    /**
     * returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        return $this->service->getDefaultLanguageCode();
    }

    /**
     * Instantiates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        return $this->service->newLanguageCreateStruct();
    }
}
