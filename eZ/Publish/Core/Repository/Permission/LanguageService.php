<?php
/**
 * File containing the eZ\Publish\Core\Repository\Permission\LanguageService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository\Permission
 */

namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\SPI\Persistence\Content\Language\Handler;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Language as SPILanguage;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use LogicException;
use Exception;

/**
 * Language service, used for language operations
 *
 * @package eZ\Publish\Core\Repository\Permission
 */
class LanguageService implements LanguageServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $permissionsService;

    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $innerLanguageService;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\LanguageService $innerLanguageService
     * @param PermissionsService $permissionsService
     */
    public function __construct(
        LanguageServiceInterface $innerLanguageService,
        PermissionsService $permissionsService
    )
    {
        $this->innerLanguageService = $innerLanguageService;
        $this->permissionsService = $permissionsService;
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
        if ( $this->permissionsService->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        return $this->innerLanguageService->createLanguage( $languageCreateStruct );
    }

    /**
     * Changes the name of the language in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageCode argument
     *         is not string
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     * @param string $newName
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function updateLanguageName( Language $language, $newName )
    {
        if ( $this->permissionsService->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        return $this->innerLanguageService->updateLanguageName( $language, $newName );
    }

    /**
     * Enables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function enableLanguage( Language $language )
    {
        if ( $this->permissionsService->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        return $this->innerLanguageService->enableLanguage( $language );
    }

    /**
     * Disables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function disableLanguage( Language $language )
    {
        if ( $this->permissionsService->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        return $this->innerLanguageService->disableLanguage( $language );
    }

    /**
     * Loads a Language from its language code ($languageCode)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageCode argument
     *         is not string
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguage( $languageCode )
    {
        return $this->innerLanguageService->loadLanguage( $languageCode );
    }

    /**
     * Loads all Languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    public function loadLanguages()
    {
        return $this->innerLanguageService->loadLanguages();
    }

    /**
     * Loads a Language by its id ($languageId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param mixed $languageId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguageById( $languageId )
    {
        return $this->innerLanguageService->loadLanguageById( $languageId );
    }

    /**
     * Deletes  a language from content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if language can not be deleted
     *         because it is still assigned to some content / type / (...).
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function deleteLanguage( Language $language )
    {
        if ( $this->permissionsService->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        return $this->innerLanguageService->deleteLanguage( $language );
    }

    /**
     * Returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        return $this->innerLanguageService->getDefaultLanguageCode();
    }

    /**
     * Instantiates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        return $this->innerLanguageService->newLanguageCreateStruct();
    }
}
