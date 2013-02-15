<?php
/**
 * File containing the LanguageServiceStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\LanguageService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\LanguageService
 */
class LanguageServiceStub implements LanguageService
{
    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\ContentServiceStub
     */
    private $contentService;

    /**
     * @var string
     */
    private $defaultLanguageCode;

    /**
     * @var int
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
     * Instantiates the language service.
     *
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     * @param \eZ\Publish\API\Repository\Tests\Stubs\ContentServiceStub $contentService
     * @param string $defaultLanguageCode
     */
    public function __construct(
        RepositoryStub $repository,
        ContentServiceStub $contentService,
        $defaultLanguageCode
    )
    {
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->defaultLanguageCode = $defaultLanguageCode;

        $this->initFromFixture();
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
        if ( isset( $this->codes[$languageCreateStruct->languageCode] ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }
        if ( true !== $this->repository->hasAccess( 'content', 'translations' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $language = new Language(
            array(
                'id' => ++$this->nextId,
                'name' => $languageCreateStruct->name,
                'enabled' => $languageCreateStruct->enabled,
                'languageCode' => $languageCreateStruct->languageCode
            )
        );

        $this->languages[$language->id] = $language;
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
        if ( true !== $this->repository->hasAccess( 'content', 'translations' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->languages[$language->id] = new Language(
            array(
                'id' => $language->id,
                'name' => $newName,
                'enabled' => $language->enabled,
                'languageCode' => $language->languageCode
            )
        );

        return $this->languages[$language->id];
    }

    /**
     * Enables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function enableLanguage( Language $language )
    {
        if ( true !== $this->repository->hasAccess( 'content', 'translations' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->languages[$language->id] = new Language(
            array(
                'id' => $language->id,
                'name' => $language->name,
                'enabled' => true,
                'languageCode' => $language->languageCode
            )
        );
    }

    /**
     * Disables a language
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If user does not have access to content translations
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Language $language
     */
    public function disableLanguage( Language $language )
    {
        if ( true !== $this->repository->hasAccess( 'content', 'translations' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->languages[$language->id] = new Language(
            array(
                'id' => $language->id,
                'name' => $language->name,
                'enabled' => false,
                'languageCode' => $language->languageCode
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
        throw new NotFoundExceptionStub( 'What error code should be used?' );
    }

    /**
     * Loads all Languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
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
        throw new NotFoundExceptionStub( 'What error code should be used?' );
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
        if ( true !== $this->repository->hasAccess( 'content', 'translations' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( count( $this->contentService->loadContentInfoByLanguageCode( $language->languageCode ) ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }
        unset( $this->languages[$language->id], $this->codes[$language->languageCode] );
    }

    /**
     * Returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        return $this->defaultLanguageCode;
    }

    /**
     * Instantiates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        return new LanguageCreateStruct();
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @access private
     *
     * @internal
     *
     * @return void
     */
    public function rollback()
    {
        $this->initFromFixture();
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        list(
            $this->languages,
            $this->codes,
            $this->nextId
        ) = $this->repository->loadFixture( 'Language' );
    }
}
