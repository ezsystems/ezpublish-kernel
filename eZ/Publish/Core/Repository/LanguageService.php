<?php
/**
 * File containing the eZ\Publish\Core\Repository\LanguageService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;
use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface,
    eZ\Publish\SPI\Persistence\Content\Language\Handler,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,

    eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Language as SPILanguage,
    eZ\Publish\SPI\Persistence\Content\Language\CreateStruct,

    eZ\Publish\API\Repository\Values\Content\Language,

    eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\UnauthorizedException,
    LogicException;

/**
 * Language service, used for language operations
 *
 * @package eZ\Publish\Core\Repository
 */
class LanguageService implements LanguageServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $languageHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->languageHandler = $languageHandler;
        $this->settings = $settings + array(// Union makes sure default settings are ignored if provided in argument
            'languages' => array( 'eng-GB' ),
        );
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
        if ( !is_string( $languageCreateStruct->name ) || empty( $languageCreateStruct->name ) )
            throw new InvalidArgumentValue( "name", $languageCreateStruct->name, "LanguageCreateStruct" );

        if ( !is_string( $languageCreateStruct->languageCode ) || empty( $languageCreateStruct->languageCode ) )
            throw new InvalidArgumentValue( "languageCode", $languageCreateStruct->languageCode, "LanguageCreateStruct" );

        if ( !is_bool( $languageCreateStruct->enabled ) )
            throw new InvalidArgumentValue( "enabled", $languageCreateStruct->enabled, "LanguageCreateStruct" );

        if ( $this->repository->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        try
        {
            if ( $this->loadLanguage( $languageCreateStruct->languageCode ) !== null )
                throw new InvalidArgumentException( "languageCreateStruct", "language with specified language code already exists" );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        $createStruct = new CreateStruct(
            array(
                'languageCode' => $languageCreateStruct->languageCode,
                'name' => $languageCreateStruct->name,
                'isEnabled' => $languageCreateStruct->enabled
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $createdLanguage = $this->languageHandler->create( $createStruct );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainObject( $createdLanguage );
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
        if ( !is_numeric( $language->id ) )
            throw new InvalidArgumentValue( "id", $language->id, "Language" );

        if ( !is_string( $newName ) || empty( $newName ) )
            throw new InvalidArgumentValue( "newName", $newName );

        if ( $this->repository->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        $loadedLanguage = $this->loadLanguageById( $language->id );

        $updateLanguageStruct = new SPILanguage(
            array(
                'id' => $loadedLanguage->id,
                'languageCode' => $loadedLanguage->languageCode,
                'name' => $newName,
                'isEnabled' => $loadedLanguage->enabled
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $this->languageHandler->update( $updateLanguageStruct );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadLanguageById( $loadedLanguage->id );
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
        if ( !is_numeric( $language->id ) )
            throw new InvalidArgumentValue( "id", $language->id );

        if ( $this->repository->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        $loadedLanguage = $this->loadLanguageById( $language->id );

        $updateLanguageStruct = new SPILanguage(
            array(
                'id' => $loadedLanguage->id,
                'languageCode' => $loadedLanguage->languageCode,
                'name' => $loadedLanguage->name,
                'isEnabled' => true
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $this->languageHandler->update( $updateLanguageStruct );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadLanguageById( $loadedLanguage->id );
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
        if ( !is_numeric( $language->id ) )
            throw new InvalidArgumentValue( "id", $language->id );

        if ( $this->repository->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        $loadedLanguage = $this->loadLanguageById( $language->id );

        $updateLanguageStruct = new SPILanguage(
            array(
                'id' => $loadedLanguage->id,
                'languageCode' => $loadedLanguage->languageCode,
                'name' => $loadedLanguage->name,
                'isEnabled' => false
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $this->languageHandler->update( $updateLanguageStruct );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadLanguageById( $loadedLanguage->id );
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
        if ( !is_string( $languageCode ) || empty( $languageCode ) )
            throw new InvalidArgumentException( "languageCode", "language code has an invalid value" );

        $language = $this->languageHandler->loadByLanguageCode( $languageCode );
        return $this->buildDomainObject( $language );
    }

    /**
     * Loads all Languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    public function loadLanguages()
    {
        $languages = $this->languageHandler->loadAll();

        $returnArray = array();
        foreach ( $languages as $language )
        {
            $returnArray[] = $this->buildDomainObject( $language );
        }

        return $returnArray;
    }

    /**
     * Loads a Language by its id ($languageId)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageId argument
     *         is not integer
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if language could not be found
     *
     * @param int $languageId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    public function loadLanguageById( $languageId )
    {
        if ( !is_numeric( $languageId ) )
            throw new InvalidArgumentValue( "languageId", $languageId );

        $language = $this->languageHandler->load( (int) $languageId );

        return $this->buildDomainObject( $language );
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
        if ( !is_numeric( $language->id ) )
            throw new InvalidArgumentValue( "id", $language->id, "Language" );

        if ( $this->repository->hasAccess( 'content', 'translations' ) !== true )
            throw new UnauthorizedException( 'content', 'translations' );

        $loadedLanguage = $this->loadLanguageById( $language->id );

        $this->repository->beginTransaction();
        try
        {
            $this->languageHandler->delete( $loadedLanguage->id );
            $this->repository->commit();
        }
        catch ( LogicException $e )
        {
            $this->repository->rollback();
            throw new InvalidArgumentException( "language", $e->getMessage(), $e );
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        return $this->settings['languages'][0];
    }

    /**
     * Returns a configured list of prioritized languageCodes
     *
     * @access private This is currently only for internal use in Services
     * @return string[]
     */
    public function getPrioritizedLanguageCodeList()
    {
        return $this->settings['languages'];
    }

    /**
     * instantiates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        return new LanguageCreateStruct();
    }

    /**
     * Builds Language domain object from ValueObject returned by Persistence API
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $spiLanguage
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    protected function buildDomainObject( SPILanguage $spiLanguage )
    {
        return new Language(
            array(
                'id' => $spiLanguage->id,
                'languageCode' => $spiLanguage->languageCode,
                'name' => $spiLanguage->name,
                'enabled' => $spiLanguage->isEnabled
            )
        );
    }
}
