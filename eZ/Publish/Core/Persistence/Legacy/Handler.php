<?php
/**
 * File containing the Handler interface
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy;

use eZ\Publish\SPI\Persistence\Handler as HandlerInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\Type;
use eZ\Publish\Core\Persistence\Legacy\Content\Handler as ContentHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler as ContentFieldHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler as TypeHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper as TypeMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper as LanguageMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler as LocationHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Handler as LocationSearchHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Trash\Handler as TrashHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper as ObjectStateMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler as ContentCriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway\CriterionHandler as LocationCriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler as CommonCriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler as UrlAliasHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper as UrlAliasMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase as UrlAliasGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\ExceptionConversion as UrlAliasExceptionConversionGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Handler as UrlWildcardHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Mapper as UrlWildcardMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase as UrlWildcardGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\SortClauseHandler as ContentSortClauseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway\SortClauseHandler as LocationSortClauseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseHandler as CommonSortClauseHandler;
use eZ\Publish\Core\Persistence\Legacy\User\Mapper as UserMapper;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationHandler\ObjectStateHandler as ObjectStateLimitationHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as ConverterRegistry;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use Exception;
use RuntimeException;

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
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\MemoryCachingHandler
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
     * Location search handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Handler
     */
    protected $locationSearchHandler;

    /**
     * Location gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location search gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway
     */
    protected $locationSearchGateway;

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
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
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
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
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
     * FieldType registry
     *
     * @var \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    protected $fieldTypeRegistry;

    /**
     * Transform Processor
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\FieldValue\Converter
     */
    protected $criterionFieldValueConverter;

    /**
     * General configuration
     *
     * @var array
     */
    protected $config;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * Creates a new repository handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler The database handler
     * @param \eZ\Publish\Core\Persistence\FieldTypeRegistry $fieldTypeRegistry Should contain field types
     * @param Content\FieldValue\ConverterRegistry $converterRegistry Should contain Field Type converters
     * @param Content\StorageRegistry $storageRegistry Should contain Field Type external storage handlers
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor Search Text Transformation processor
     * @param array $config List of optional configuration flags:
     *                      The flag 'defer_type_update' defines if content types should be
     *                      published immediately (false), when the
     *                      {@link \eZ\Publish\SPI\Persistence\Content\Type\Handler::publish()} method
     *                      is called, or if a background process should be triggered (true), which
     *                      is then executed by the old eZ Publish core.
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        FieldTypeRegistry $fieldTypeRegistry,
        ConverterRegistry $converterRegistry,
        StorageRegistry $storageRegistry,
        TransformationProcessor $transformationProcessor,
        array $config = array()
    )
    {
        $this->dbHandler = $dbHandler;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->converterRegistry = $converterRegistry;
        $this->storageRegistry = $storageRegistry;
        $this->transformationProcessor = $transformationProcessor;
        $this->config = $config;

    }

    /**
     * @todo remove circular dependency with LocationHandler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    public function contentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new ContentHandler(
                $this->getContentGateway(),
                $this->getLocationGateway(),
                $this->getContentMapper(),
                $this->getFieldHandler(),
                $this->getSlugConverter(),
                $this->getUrlAliasGateway()
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
                new Content\Gateway\DoctrineDatabase(
                    $this->dbHandler,
                    new Content\Gateway\DoctrineDatabase\QueryBuilder( $this->dbHandler ),
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
     * @todo remove circular dependency with ContentTypeHandler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandler()
    {
        if ( !isset( $this->fieldHandler ) )
        {
            $this->fieldHandler = new ContentFieldHandler(
                $this->getContentGateway(),
                $this->getContentMapper(),
                $this->getStorageHandler(),
                $this->contentLanguageHandler(),
                $this->getFieldTypeRegistry()
            );
            $this->fieldHandler->typeHandler = $this->contentTypeHandler();
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
     * Returns the field type registry
     *
     * @return \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    public function getFieldTypeRegistry()
    {
        return $this->fieldTypeRegistry;
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

    protected function getCriterionFieldValueConverter()
    {
        if ( !isset( $this->criterionFieldValueConverter ) )
        {
            $db = $this->dbHandler;
            $commaSeparatedCollectionValueHandler = new CommonCriterionHandler\FieldValue\Handler\Collection(
                $db, $this->transformationProcessor, ","
            );
            $hyphenSeparatedCollectionValueHandler = new CommonCriterionHandler\FieldValue\Handler\Collection(
                $db, $this->transformationProcessor, "-"
            );
            $compositeValueHandler = new CommonCriterionHandler\FieldValue\Handler\Composite(
                $db, $this->transformationProcessor
            );
            $simpleValueHandler = new CommonCriterionHandler\FieldValue\Handler\Simple(
                $db, $this->transformationProcessor
            );

            $this->criterionFieldValueConverter = new CommonCriterionHandler\FieldValue\Converter(
                new CommonCriterionHandler\FieldValue\HandlerRegistry(
                    array(
                        "ezboolean" => $simpleValueHandler,
                        "ezcountry" => $commaSeparatedCollectionValueHandler,
                        "ezdate" => $simpleValueHandler,
                        "ezdatetime" => $simpleValueHandler,
                        "ezemail" => $simpleValueHandler,
                        "ezinteger" => $simpleValueHandler,
                        "ezobjectrelation" => $simpleValueHandler,
                        "ezobjectrelationlist" => $commaSeparatedCollectionValueHandler,
                        "ezselection" => $hyphenSeparatedCollectionValueHandler,
                        "eztime" => $simpleValueHandler,
                    )
                ),
                $compositeValueHandler
            );
        }
        return $this->criterionFieldValueConverter;
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
                    new Content\Search\Gateway\DoctrineDatabase(
                        $db,
                        new Content\Search\Common\Gateway\CriteriaConverter(
                            array(
                                new CommonCriterionHandler\MatchAll( $db ),
                                new CommonCriterionHandler\ContentId( $db ),
                                new CommonCriterionHandler\LogicalNot( $db ),
                                new CommonCriterionHandler\LogicalAnd( $db ),
                                new CommonCriterionHandler\LogicalOr( $db ),
                                new ContentCriterionHandler\Subtree( $db ),
                                new CommonCriterionHandler\ContentTypeId( $db ),
                                new CommonCriterionHandler\ContentTypeIdentifier( $db ),
                                new CommonCriterionHandler\ContentTypeGroupId( $db ),
                                new CommonCriterionHandler\DateMetadata( $db ),
                                new ContentCriterionHandler\LocationId( $db ),
                                new ContentCriterionHandler\LocationPriority( $db ),
                                new ContentCriterionHandler\ParentLocationId( $db ),
                                new CommonCriterionHandler\RemoteId( $db ),
                                new ContentCriterionHandler\LocationRemoteId( $db ),
                                new CommonCriterionHandler\SectionId( $db ),
                                new CommonCriterionHandler\FullText(
                                    $db,
                                    $this->transformationProcessor
                                ),
                                new CommonCriterionHandler\Field(
                                    $db,
                                    $this->converterRegistry,
                                    $this->getCriterionFieldValueConverter(),
                                    $this->transformationProcessor
                                ),
                                new CommonCriterionHandler\ObjectStateId( $db ),
                                new CommonCriterionHandler\LanguageCode(
                                    $db,
                                    $this->getLanguageMaskGenerator()
                                ),
                                new ContentCriterionHandler\Visibility( $db ),
                                new CommonCriterionHandler\UserMetadata( $db ),
                                new CommonCriterionHandler\RelationList( $db ),
                                new ContentCriterionHandler\Depth( $db ),
                                new CommonCriterionHandler\MapLocationDistance( $db ),
                            )
                        ),
                        new Content\Search\Common\Gateway\SortClauseConverter(
                            array(
                                new ContentSortClauseHandler\LocationPathString( $db ),
                                new ContentSortClauseHandler\LocationDepth( $db ),
                                new ContentSortClauseHandler\LocationPriority( $db ),
                                new CommonSortClauseHandler\DateModified( $db ),
                                new CommonSortClauseHandler\DatePublished( $db ),
                                new CommonSortClauseHandler\SectionIdentifier( $db ),
                                new CommonSortClauseHandler\SectionName( $db ),
                                new CommonSortClauseHandler\ContentName( $db ),
                                new CommonSortClauseHandler\ContentId( $db ),
                                new CommonSortClauseHandler\Field( $db, $this->contentLanguageHandler() ),
                                new CommonSortClauseHandler\MapLocationDistance( $db, $this->contentLanguageHandler() ),
                            )
                        ),
                        new Content\Gateway\DoctrineDatabase\QueryBuilder( $this->dbHandler ),
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
     * @todo remove circular dependency with FieldHandler
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        $this->getFieldHandler();
        if ( !isset( $this->contentTypeHandler ) )
        {
            $this->contentTypeHandler = new Content\Type\MemoryCachingHandler(
                new TypeHandler(
                    $this->getContentTypeGateway(),
                    new TypeMapper( $this->converterRegistry ),
                    $this->getTypeUpdateHandler()
                )
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
                    $this->getContentTypeGateway()
                );
            }
            else
            {
                $this->typeUpdateHandler = new Type\Update\Handler\DoctrineDatabase(
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
                new Type\Gateway\DoctrineDatabase(
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
            $this->languageHandler = new Content\Language\CachingHandler(
                new Content\Language\Handler(
                    new Content\Language\Gateway\ExceptionConversion(
                        new Content\Language\Gateway\DoctrineDatabase( $this->dbHandler )
                    ),
                    new LanguageMapper()
                ),
                $this->getLanguageCache()
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
     * @todo remove circular dependency with ContentHandler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler
     */
    public function locationHandler()
    {
        $this->contentHandler();
        if ( !isset( $this->locationHandler ) )
        {
            $this->locationHandler = new LocationHandler(
                $this->getLocationGateway(),
                $this->getLocationMapper(),
                $this->contentHandler(),
                $this->getContentMapper(),
                $this->objectStateHandler()
            );
        }
        return $this->locationHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Search\Handler
     */
    public function locationSearchHandler()
    {
        if ( !isset( $this->locationSearchHandler ) )
        {
            $this->locationSearchHandler = new LocationSearchHandler(
                $this->getLocationSearchGateway(),
                $this->getLocationMapper()
            );
        }
        return $this->locationSearchHandler;
    }

    /**
     * Returns a location gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway\DoctrineDatabase
     */
    protected function getLocationSearchGateway()
    {
        if ( !isset( $this->locationSearchGateway ) )
        {
            $dbHandler = $this->dbHandler;
            $this->locationSearchGateway = new Content\Search\Location\Gateway\ExceptionConversion(
                new Content\Search\Location\Gateway\DoctrineDatabase(
                    $dbHandler,
                    new Content\Search\Common\Gateway\CriteriaConverter(
                        array(
                            new LocationCriterionHandler\Location\Id( $dbHandler ),
                            new LocationCriterionHandler\Location\Depth( $dbHandler ),
                            new LocationCriterionHandler\Location\ParentLocationId( $dbHandler ),
                            new LocationCriterionHandler\Location\Priority( $dbHandler ),
                            new LocationCriterionHandler\Location\RemoteId( $dbHandler ),
                            new LocationCriterionHandler\Location\Subtree( $dbHandler ),
                            new LocationCriterionHandler\Location\Visibility( $dbHandler ),
                            new LocationCriterionHandler\Location\IsMainLocation( $dbHandler ),
                            new CommonCriterionHandler\ContentId( $dbHandler ),
                            new CommonCriterionHandler\ContentTypeGroupId( $dbHandler ),
                            new CommonCriterionHandler\ContentTypeId( $dbHandler ),
                            new CommonCriterionHandler\ContentTypeIdentifier( $dbHandler ),
                            new CommonCriterionHandler\DateMetadata( $dbHandler ),
                            new CommonCriterionHandler\Field(
                                $dbHandler,
                                $this->converterRegistry,
                                $this->getCriterionFieldValueConverter(),
                                $this->transformationProcessor
                            ),
                            new CommonCriterionHandler\FullText(
                                $dbHandler,
                                $this->transformationProcessor
                            ),
                            new CommonCriterionHandler\LanguageCode(
                                $dbHandler,
                                $this->getLanguageMaskGenerator()
                            ),
                            new CommonCriterionHandler\LogicalAnd( $dbHandler ),
                            new CommonCriterionHandler\LogicalNot( $dbHandler ),
                            new CommonCriterionHandler\LogicalOr( $dbHandler ),
                            new CommonCriterionHandler\MapLocationDistance( $dbHandler ),
                            new CommonCriterionHandler\MatchAll( $dbHandler ),
                            new CommonCriterionHandler\ObjectStateId( $dbHandler ),
                            new CommonCriterionHandler\RelationList( $dbHandler ),
                            new CommonCriterionHandler\RemoteId( $dbHandler ),
                            new CommonCriterionHandler\SectionId( $dbHandler ),
                            new CommonCriterionHandler\UserMetadata( $dbHandler ),
                        )
                    ),
                    new Content\Search\Common\Gateway\SortClauseConverter(
                        array(
                            new LocationSortClauseHandler\Location\Id( $dbHandler ),
                            new LocationSortClauseHandler\Location\Depth( $dbHandler ),
                            new LocationSortClauseHandler\Location\Path( $dbHandler ),
                            new LocationSortClauseHandler\Location\Priority( $dbHandler ),
                            new LocationSortClauseHandler\Location\Visibility( $dbHandler ),
                            new LocationSortClauseHandler\Location\IsMainLocation( $dbHandler ),
                            new CommonSortClauseHandler\ContentId( $dbHandler ),
                            new CommonSortClauseHandler\ContentName( $dbHandler ),
                            new CommonSortClauseHandler\DateModified( $dbHandler ),
                            new CommonSortClauseHandler\DatePublished( $dbHandler ),
                            new CommonSortClauseHandler\SectionIdentifier( $dbHandler ),
                            new CommonSortClauseHandler\SectionName( $dbHandler ),
                            new CommonSortClauseHandler\Field(
                                $dbHandler,
                                $this->contentLanguageHandler()
                            ),
                            new CommonSortClauseHandler\MapLocationDistance(
                                $dbHandler,
                                $this->contentLanguageHandler()
                            ),
                        )
                    )
                )
            );
        }
        return $this->locationSearchGateway;
    }

    /**
     * Returns a location gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase
     */
    protected function getLocationGateway()
    {
        if ( !isset( $this->locationGateway ) )
        {
            $dbHandler = $this->dbHandler;
            $this->locationGateway = new Content\Location\Gateway\ExceptionConversion(
                new Content\Location\Gateway\DoctrineDatabase( $dbHandler )
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase
     */
    protected function getObjectStateGateway()
    {
        if ( !isset( $this->objectStateGateway ) )
        {
            $this->objectStateGateway = new Content\ObjectState\Gateway\ExceptionConversion(
                new Content\ObjectState\Gateway\DoctrineDatabase(
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
                    new User\Gateway\DoctrineDatabase( $this->dbHandler )
                ),
                new User\Role\Gateway\DoctrineDatabase( $this->dbHandler ),
                new UserMapper(),
                new LimitationConverter( array( new ObjectStateLimitationHandler( $this->dbHandler ) ) )
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
                    new Content\Section\Gateway\DoctrineDatabase( $this->dbHandler )
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
                $this->getSlugConverter()
            );
        }

        return $this->urlAliasHandler;
    }

    /**
     * Returns a UrlAlias gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase
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
     * Returns a slug converter
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
     */
    protected function getSlugConverter()
    {
        if ( !isset( $this->slugConverter ) )
        {
            $this->slugConverter = new SlugConverter(
                $this->transformationProcessor
            );
        }
        return $this->slugConverter;
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\DoctrineDatabase
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
        catch ( Exception $e )
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

            // Clear all caches after rollback
            if ( isset( $this->contentTypeHandler ) )
                $this->contentTypeHandler->clearCache();

            if ( isset( $this->languageHandler ) )
                $this->languageHandler->clearCache();
        }
        catch ( Exception $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }
}
