<?php
/**
 * @package eZ\Publish\API\Repository
 */
namespace eZ\Publish\Core\Repository;
use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface,
    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Language as PersistenceLanguage,
    eZ\Publish\SPI\Persistence\Content\Language\CreateStruct,
    eZ\Publish\API\Repository\Values\Content\Language,
    eZ\Publish\Core\Base\ConfigurationManager,
    eZ\Publish\Core\Base\Exceptions\NotFound,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\IllegalArgumentException,
    ezp\Base\Exception\NotFound as PersistenceNotFound;

/**
 * Language service, used for language operations
 *
 * @package ezp\Publish\PublicAPI
 */
class LanguageService implements LanguageServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
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
        try
        {
            if ( $this->loadLanguage( $languageCreateStruct->languageCode ) !== null )
                throw new IllegalArgumentException( "languageCreateStruct", $languageCreateStruct->languageCode );
        }
        catch ( NotFound $e ) {}

        $createStruct = new CreateStruct(
            array(
                'languageCode' => $languageCreateStruct->languageCode,
                'name' => $languageCreateStruct->name,
                'isEnabled' => $languageCreateStruct->enabled
            )
        );
        $createdLanguage = $this->handler->contentLanguageHandler()->create( $createStruct );

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
        if ( empty( $language->id ) )
            throw new InvalidArgumentValue( "id", $language->id );

        $updateLanguageStruct = new PersistenceLanguage(
            array(
                'id' => $language->id,
                'languageCode' => $language->languageCode,
                'name' => $newName,
                'isEnabled' => $language->enabled
            )
        );

        $this->handler->contentLanguageHandler()->update( $updateLanguageStruct );

        return $this->loadLanguageById( $updateLanguageStruct->id );
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
        if ( empty( $language->id ) )
            throw new InvalidArgumentValue( "id", $language->id );

        $updateLanguageStruct = new PersistenceLanguage(
            array(
                'id' => $language->id,
                'languageCode' => $language->languageCode,
                'name' => $language->name,
                'isEnabled' => true
            )
        );

        $this->handler->contentLanguageHandler()->update( $updateLanguageStruct );
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
        if ( empty( $language->id ) )
            throw new InvalidArgumentValue( "id", $language->id );

        $updateLanguageStruct = new PersistenceLanguage(
            array(
                'id' => $language->id,
                'languageCode' => $language->languageCode,
                'name' => $language->name,
                'isEnabled' => false
            )
        );

        $this->handler->contentLanguageHandler()->update( $updateLanguageStruct );
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
        if ( !is_string( $languageCode ) )
            throw new InvalidArgumentValue( "languageCode", $languageCode );

        try
        {
            $language = $this->handler->contentLanguageHandler()->loadByLanguageCode( $languageCode );
        }
        catch ( PersistenceNotFound $e )
        {
            throw new NotFound( "language", $languageCode, $e );
        }

        return $this->buildDomainObject( $language );
    }

    /**
     * Loads all Languages
     *
     * @return array an array of {@link  \eZ\Publish\API\Repository\Values\Content\Language}
     */
    public function loadLanguages()
    {
        $languages = $this->handler->contentLanguageHandler()->loadAll();
        $returnArray = array();

        foreach ( $languages as $languageCode => $language )
        {
            $returnArray[$languageCode] = $this->buildDomainObject( $language );
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
        if ( !is_int( $languageId ) )
            throw new InvalidArgumentValue( "languageId", $languageId );

        try
        {
            $language = $this->handler->contentLanguageHandler()->load( $languageId );
        }
        catch ( PersistenceNotFound $e )
        {
            throw new NotFound( "Language", $languageId, $e );
        }

        return $this->buildDomainObject( $language );
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
     *
     * @todo implement properly when it is possible to count translation content
     */
    public function deleteLanguage( Language $language )
    {
        $language = $this->loadLanguageById( $language->id );
        $this->handler->contentLanguageHandler()->delete( $language->id );
    }

    /**
     * returns a configured default language code
     *
     * @return string
     */
    public function getDefaultLanguageCode()
    {
        $settings = include( __DIR__ . "/../../../../config.php" );
        $configManager = new ConfigurationManager( $settings, $settings["base"]["Configuration"]["Paths"] );
        $siteLanguageList = $configManager->getConfiguration("site")->get( "RegionalSettings", "SiteLanguageList" );

        return reset( $siteLanguageList );
    }

    /**
     * instanciates an object to be used for creating languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct
     */
    public function newLanguageCreateStruct()
    {
        return new LanguageCreateStruct;
    }

    /**
     * Builds Language domain object from ValueObject returned by Persistence API
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $vo Language value object
     *        (extending \eZ\Publish\SPI\Persistence\ValueObject) returned by persistence
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    protected function buildDomainObject( PersistenceLanguage $vo )
    {
        return new Language(
            array(
                'id' => $vo->id,
                'languageCode' => $vo->languageCode,
                'name' => $vo->name,
                'enabled' => $vo->isEnabled
            )
        );
    }
}
