<?php
/**
 * File containing the Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\SPI\Persistence\Handler as HandlerInterface,
    eZ\Publish\Core\Persistence\Legacy\Content\Type,
    eZ\Publish\Core\Persistence\Legacy\Content\Handler as ContentHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler as ContentFieldHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler as TypeHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper as TypeMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper as LanguageMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler as LocationHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash\Handler as TrashHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler as ObjectStateHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper as ObjectStateMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler as UrlAliasHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper as UrlAliasMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\EzcDatabase as UrlAliasGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\ExceptionConversion as UrlAliasExceptionConversionGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Handler as UrlWildcardHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper as UrlWildcardMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase as UrlWildcardGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\SortClauseHandler,
    eZ\Publish\Core\Persistence\Legacy\User\Mapper as UserMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as ConverterRegistry,
    ezcDbTransactionException,
    RuntimeException;

/**
 * The repository handler for the legacy storage engine
 */
class Handler implements HandlerInterface
{
    /**
     * Content handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Content mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Storage handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Field handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected $fieldHandler;

    /**
     * Search handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * Content type handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Content Type gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected $contentTypeGateway;

    /**
     * Content Type update handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler
     */
    protected $typeUpdateHandler;

    /**
     * Location handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Location gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * ObjectState handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * ObjectState gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    protected $objectStateGateway;

    /**
     * ObjectState mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected $objectStateMapper;

    /**
     * User handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Handler
     */
    protected $userHandler;

    /**
     * Section handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Trash handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash\Handler
     */
    protected $trashHandler;

    /**
     * Content gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Language handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Language cache
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected $languageCache;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * UrlAlias handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler
     */
    protected $urlAliasHandler;

    /**
     * UrlAlias gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $urlAliasGateway;

    /**
     * UrlAlias mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper
     */
    protected $urlAliasMapper;

    /**
     * UrlWildcard handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Handler
     */
    protected $urlWildcardHandler;

    /**
     * UrlWildcard gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway
     */
    protected $urlWildcardGateway;

    /**
     * UrlWildcard mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper
     */
    protected $urlWildcardMapper;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Field value converter registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $converterRegistry;

    /**
     * Storage registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Transform Processor
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * General configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Creates a new repository handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler The database handler
     * @param Content\FieldValue\ConverterRegistry $converterRegistry Should contain Field Type converters
     * @param Content\StorageRegistry $storageRegistry Should contain Field Type external storage handlers
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor $transformationProcessor Search Text Transformation processor
     * @param array $config List of optional configuration flags:
     *                      The flag 'defer_type_update' defines if content types should be
     *                      published immediately (false), when the
     *                      {@link \eZ\Publish\SPI\Persistence\Content\Type\Handler::publish()} method
     *                      is called, or if a background process should be triggered (true), which
     *                      is then executed by the old eZ Publish core.
     */
    public function __construct(
        EzcDbHandler $dbHandler,
        ConverterRegistry $converterRegistry,
        StorageRegistry $storageRegistry,
        TransformationProcessor $transformationProcessor,
        array $config = array()
    )
    {
        $this->dbHandler = $dbHandler;
        $this->converterRegistry = $converterRegistry;
        $this->storageRegistry = $storageRegistry;
        $this->transformationProcessor = $transformationProcessor;
        $this->config = $config;
    }

    /**
     * @internal LocationHandler is injected into property to avoid circular dependency
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new ContentHandler(
                $this->getContentGateway(),
                $this->getLocationGateway(),
                $this->getContentMapper(),
                $this->getFieldHandler()
            );
            $this->contentHandler->locationHandler = $this->locationHandler();
        }
        return $this->contentHandler;
    }

    /**
     * Returns a content mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapper()
    {
        if ( !isset( $this->contentMapper ) )
        {
            $this->contentMapper = new ContentMapper(
                $this->converterRegistry,
                $this->contentLanguageHandler()
            );
        }
        return $this->contentMapper;
    }

    /**
     * Returns a content gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGateway()
    {
        if ( !isset( $this->contentGateway ) )
        {
            $this->contentGateway = new Content\Gateway\ExceptionConversion(
                new Content\Gateway\EzcDatabase(
                    $this->dbHandler,
                    new Content\Gateway\EzcDatabase\QueryBuilder( $this->dbHandler ),
                    $this->contentLanguageHandler(),
                    $this->getLanguageMaskGenerator()
                )
            );
        }
        return $this->contentGateway;
    }

    /**
     * Returns a field handler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandler()
    {
        if ( !isset( $this->fieldHandler ) )
        {
            $this->fieldHandler = new ContentFieldHandler(
                $this->getContentGateway(),
                $this->getContentTypeGateway(),
                $this->getContentMapper(),
                $this->getStorageHandler()
            );
        }
        return $this->fieldHandler;
    }

    /**
     * Returns a language mask generator
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageMaskGenerator()
    {
        if ( !isset( $this->languageMaskGenerator ) )
        {
            $this->languageMaskGenerator = new Content\Language\MaskGenerator(
                $this->contentLanguageHandler()
            );
        }
        return $this->languageMaskGenerator;
    }

    /**
     * Returns the field value converter registry
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    public function getFieldValueConverterRegistry()
    {
        return $this->converterRegistry;
    }
    /**
     * Returns the storage registry
     *
     * @return Content\StorageRegistry
     */
    public function getStorageRegistry()
    {
        return $this->storageRegistry;
    }

    /**
     * Returns a storage handler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected function getStorageHandler()
    {
        if ( !isset( $this->storageHandler ) )
        {
            $this->storageHandler = new StorageHandler(
                $this->storageRegistry,
                $this->getContentGateway()->getContext()
            );
        }
        return $this->storageHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function searchHandler()
    {
        if ( !isset( $this->searchHandler ) )
        {
            $db = $this->dbHandler;
            $this->searchHandler = new Content\Search\Handler(
                new Content\Search\Gateway\ExceptionConversion(
                    new Content\Search\Gateway\EzcDatabase(
                        $db,
                        new Content\Search\Gateway\CriteriaConverter(
                            array(
                                new CriterionHandler\ContentId( $db ),
                                new CriterionHandler\LogicalNot( $db ),
                                new CriterionHandler\LogicalAnd( $db ),
                                new CriterionHandler\LogicalOr( $db ),
                                new CriterionHandler\Subtree( $db ),
                                new CriterionHandler\ContentTypeId( $db ),
                                new CriterionHandler\ContentTypeGroupId( $db ),
                                new CriterionHandler\DateMetadata( $db ),
                                new CriterionHandler\LocationId( $db ),
                                new CriterionHandler\ParentLocationId( $db ),
                                new CriterionHandler\RemoteId( $db ),
                                new CriterionHandler\LocationRemoteId( $db ),
                                new CriterionHandler\SectionId( $db ),
                                new CriterionHandler\Status( $db ),
                                new CriterionHandler\FullText(
                                    $db,
                                    $this->transformationProcessor
                                ),
                                new CriterionHandler\Field(
                                    $db,
                                    $this->converterRegistry
                                ),
                                new CriterionHandler\ObjectStateId( $db ),
                                new CriterionHandler\LanguageCode(
                                    $db,
                                    $this->getLanguageMaskGenerator()
                                ),
                                new CriterionHandler\Visibility( $db ),
                            )
                        ),
                        new Content\Search\Gateway\SortClauseConverter(
                            array(
                                new SortClauseHandler\LocationPathString( $db ),
                                new SortClauseHandler\LocationDepth( $db ),
                                new SortClauseHandler\LocationPriority( $db ),
                                new SortClauseHandler\DateModified( $db ),
                                new SortClauseHandler\DatePublished( $db ),
                                new SortClauseHandler\SectionIdentifier( $db ),
                                new SortClauseHandler\SectionName( $db ),
                                new SortClauseHandler\ContentName( $db ),
                                new SortClauseHandler\ContentId( $db ),
                                new SortClauseHandler\Field( $db ),
                            )
                        ),
                        new Content\Gateway\EzcDatabase\QueryBuilder( $this->dbHandler ),
                        $this->contentLanguageHandler(),
                        $this->getLanguageMaskGenerator()
                    )
                ),
                $this->getContentMapper(),
                $this->getFieldHandler()
            );
        }
        return $this->searchHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        if ( !isset( $this->contentTypeHandler ) )
        {
            $this->contentTypeHandler = new TypeHandler(
                $this->getContentTypeGateway(),
                new TypeMapper( $this->converterRegistry ),
                $this->getTypeUpdateHandler()
            );
        }
        return $this->contentTypeHandler;
    }

    /**
     * Returns a Content Type update handler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler
     */
    protected function getTypeUpdateHandler()
    {
        if ( !isset( $this->typeUpdateHandler ) )
        {
            if ( isset( $this->config['defer_type_update'] ) && $this->config['defer_type_update'] )
            {
                $this->typeUpdateHandler = new Type\Update\Handler\DeferredLegacy(
                    $this->getContentGateway()
                );
            }
            else
            {
                $this->typeUpdateHandler = new Type\Update\Handler\EzcDatabase(
                    $this->getContentTypeGateway(),
                    new Type\ContentUpdater(
                        $this->searchHandler(),
                        $this->getContentGateway(),
                        $this->converterRegistry,
                        $this->getStorageHandler()
                    )
                );
            }
        }
        return $this->typeUpdateHandler;
    }

    /**
     * Returns the content type gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected function getContentTypeGateway()
    {
        if ( !isset( $this->contentTypeGateway ) )
        {
            $this->contentTypeGateway = new Type\Gateway\ExceptionConversion(
                new Type\Gateway\EzcDatabase(
                    $this->dbHandler,
                    $this->getLanguageMaskGenerator()
                )
            );
        }
        return $this->contentTypeGateway;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler()
    {
        if ( !isset( $this->languageHandler ) )
        {
            /**
             * Caching language handler, not suitable for testing
             *
            $this->languageHandler = new Content\Language\CachingHandler(
                new Content\Language\Handler(
                    new Content\Language\Gateway\ExceptionConversion(
                        new Content\Language\Gateway\EzcDatabase( $this->dbHandler )
                    ),
                    new LanguageMapper()
                ),
                $this->getLanguageCache()
            );
            */

            $this->languageHandler = new Content\Language\Handler(
                new Content\Language\Gateway\ExceptionConversion(
                    new Content\Language\Gateway\EzcDatabase( $this->dbHandler )
                ),
                new LanguageMapper()
            );
        }
        return $this->languageHandler;
    }

    /**
     * Returns a Language cache
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected function getLanguageCache()
    {
        if ( !isset( $this->languageCache ) )
        {
            $this->languageCache = new Content\Language\Cache();
        }
        return $this->languageCache;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function locationHandler()
    {
        if ( !isset( $this->locationHandler ) )
        {
            $this->locationHandler = new LocationHandler(
                $this->getLocationGateway(),
                $this->getLocationMapper(),
                $this->contentHandler(),
                $this->getContentMapper()
            );
        }
        return $this->locationHandler;
    }

    /**
     * Returns a location gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase
     */
    protected function getLocationGateway()
    {
        if ( !isset( $this->locationGateway ) )
        {
            $this->locationGateway = new Content\Location\Gateway\ExceptionConversion(
                new Content\Location\Gateway\EzcDatabase( $this->dbHandler )
            );
        }
        return $this->locationGateway;
    }

    /**
     * Returns a location mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapper()
    {
        if ( !isset( $this->locationMapper ) )
        {
            $this->locationMapper = new LocationMapper();
        }
        return $this->locationMapper;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function objectStateHandler()
    {
        if ( !isset( $this->objectStateHandler ) )
        {
            $this->objectStateHandler = new ObjectStateHandler(
                $this->getObjectStateGateway(),
                $this->getObjectStateMapper()
            );
        }
        return $this->objectStateHandler;
    }

    /**
     * Returns an object state gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase
     */
    protected function getObjectStateGateway()
    {
        if ( !isset( $this->objectStateGateway ) )
        {
            $this->objectStateGateway = new Content\ObjectState\Gateway\ExceptionConversion(
                new Content\ObjectState\Gateway\EzcDatabase(
                    $this->dbHandler,
                    $this->getLanguageMaskGenerator()
                )
            );
        }
        return $this->objectStateGateway;
    }

    /**
     * Returns an object state mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected function getObjectStateMapper()
    {
        if ( !isset( $this->objectStateMapper ) )
        {
            $this->objectStateMapper = new ObjectStateMapper(
                $this->contentLanguageHandler()
            );
        }
        return $this->objectStateMapper;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function userHandler()
    {
        if ( !isset( $this->userHandler ) )
        {
            $this->userHandler = new User\Handler(
                new User\Gateway\ExceptionConversion(
                    new User\Gateway\EzcDatabase( $this->dbHandler )
                ),
                new User\Role\Gateway\EzcDatabase( $this->dbHandler ),
                new UserMapper()
            );
        }
        return $this->userHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function sectionHandler()
    {
        if ( !isset( $this->sectionHandler ) )
        {
            $this->sectionHandler = new Content\Section\Handler(
                new Content\Section\Gateway\ExceptionConversion(
                    new Content\Section\Gateway\EzcDatabase( $this->dbHandler )
                )
            );
        }
        return $this->sectionHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler()
    {
        if ( !isset( $this->trashHandler ) )
        {
            $this->trashHandler = new TrashHandler(
                $this->locationHandler(),
                $this->getLocationGateway(),
                $this->getLocationMapper(),
                $this->contentHandler()
            );
        }

        return $this->trashHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function urlAliasHandler()
    {
        if ( !isset( $this->urlAliasHandler ) )
        {
            $this->urlAliasHandler = new UrlAliasHandler(
                $this->getUrlAliasGateway(),
                $this->getUrlAliasMapper(),
                $this->getLocationGateway(),
                $this->contentLanguageHandler(),
                $this->transformationProcessor
            );
        }

        return $this->urlAliasHandler;
    }

    /**
     * Returns a UrlAlias gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\EzcDatabase
     */
    protected function getUrlAliasGateway()
    {
        if ( !isset( $this->urlAliasGateway ) )
        {
            $this->urlAliasGateway = new UrlAliasExceptionConversionGateway(
                new UrlAliasGateway(
                    $this->dbHandler,
                    $this->getLanguageMaskGenerator()
                )
            );
        }
        return $this->urlAliasGateway;
    }

    /**
     * Returns a UrlAlias mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper
     */
    protected function getUrlAliasMapper()
    {
        if ( !isset( $this->urlAliasMapper ) )
        {
            $this->urlAliasMapper = new UrlAliasMapper(
                $this->getLanguageMaskGenerator()
            );
        }
        return $this->urlAliasMapper;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function urlWildcardHandler()
    {
        if ( !isset( $this->urlWildcardHandler ) )
        {
            $this->urlWildcardHandler = new UrlWildcardHandler(
                $this->getUrlWildcardGateway(),
                $this->getUrlWildcardMapper()
            );
        }

        return $this->urlWildcardHandler;
    }

    /**
     * Returns a UrlWildcard gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase
     */
    protected function getUrlWildcardGateway()
    {
        if ( !isset( $this->urlWildcardGateway ) )
        {
            $this->urlWildcardGateway = new UrlWildcardGateway( $this->dbHandler );
        }
        return $this->urlWildcardGateway;
    }

    /**
     * Returns a UrlWildcard mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper
     */
    protected function getUrlWildcardMapper()
    {
        if ( !isset( $this->urlWildcardMapper ) )
        {
            $this->urlWildcardMapper = new UrlWildcardMapper();
        }
        return $this->urlWildcardMapper;
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->dbHandler->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit()
    {
        try
        {
            $this->dbHandler->commit();
        }
        catch ( ezcDbTransactionException $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        try
        {
            $this->dbHandler->rollback();
        }
        catch ( ezcDbTransactionException $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }
}
