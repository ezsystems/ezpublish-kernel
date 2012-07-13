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
    eZ\Publish\Core\Persistence\Legacy\Content,
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
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationParser,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationPcreCompiler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Utf8Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\SortClauseHandler,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\Core\Persistence\Legacy\User,
    eZ\Publish\Core\Persistence\Legacy\User\Mapper as UserMapper,
    ezcDbTransactionException,
    RuntimeException;

/**
 * The repository handler for the legacy storage engine
 *
 * @todo If possible, the handler should not receive the DSN but the database
 *       connection instead, so that the implementation becomes fully testable.
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
     * Field value converter registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $fieldValueConverterRegistry;

    /**
     * Storage registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Storage registry
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
     * Configurator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Configurator
     */
    protected $configurator;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new repository handler.
     *
     * The $config parameter expects an array of configuration values as
     * follows:
     *
     * <code>
     * array(
     *  'dsn' =>'<database_type>://<user>:<password>@<host>/<database_name>',
     *  'defer_type_update' => <true|false>,
     *  'external_storages' => array(
     *      '<type_name1>' => '<storage_class_1>',
     *      '<type_name2>' => '<storage_class_2>',
     *      // ...
     *  ),
     *  'field_converters' => array(
     *      '<type_name1>' => '<converter_class_1>',
     *      '<type_name2>' => '<converter_class_2>',
     *      // ...
     *  ),
     *  'transformation_rule_files' => array(
     *      '<full_file_path_1>',
     *      '<full_file_path_2>',
     *      // ...
     *  )
     * )
     * </code>
     *
     * The DSN (data source name) defines which database to use. It's format is
     * defined by the Apache Zeta Components Database component. Examples are:
     *
     * - mysql://root:secret@localhost/ezp
     *   for the MySQL database "ezp" on localhost, which will be accessed
     *   using user "root" with password "secret"
     * - sqlite://:memory:
     *   for a SQLite in memory database (used e.g. for unit tests)
     *
     * This config setting is not needed if $dbHandler is provided.
     * For further information on the database setup, please refer to
     * {@see http://incubator.apache.org/zetacomponents/documentation/trunk/Database/tutorial.html#handler-usage}
     *
     * The flag 'defer_type_update' defines if content types should be
     * published immediatly (false), when the
     * {@link \eZ\Publish\SPI\Persistence\Content\Type\Handler::publish()} method is
     * called, or if a background process should be triggered (true), which is
     * then executed by the old eZ Publish core.
     *
     * In 'external_storages' a mapping of field type names to classes is
     * expected. The referred class is instantiated and the resulting object is
     * used to store/restore/delete/… data in external storages (e.g. another
     * database or a web service). The classes must comply to the
     * {@link \eZ\Publish\SPI\Persistence\Fields\Storage} interface. Note that due to the
     * configuration mechanism and missing proper DI, the classes may not
     * expect any constructor parameters!
     *
     * The 'field_converter' configuration array consists of another mapping of
     * field type names to classes. Each of the classes is instantiated and
     * used to convert content fields and content type field definitions to the
     * legacy storage engine. The given class names must derive the
     * {@link \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter}
     * class. As for 'external_storage' classes, none of the classes may expect
     * parameters in its constructor, due to missing proper DI.
     *
     * Through the 'transformation_rule_files' array, a list of files with
     * full text transformation rules is given. These files are read by an
     * instance of
     * {@link \eZ\Publish\Core\Persistence\Legacy\Converter\Search\TransformationProcessor}
     * and then used for normalization in the full text search.
     *
     * @param array $config
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler|null $dbHandler Optional injection of db handler
     */
    public function __construct( array $config, EzcDbHandler $dbHandler = null )
    {
        $this->configurator = new Configurator( $config );
        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the Zeta Database handler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected function getDatabase()
    {
        if ( !isset( $this->dbHandler ) )
        {
            $this->dbHandler = EzcDbHandler::create( $this->configurator->getDsn() );
        }
        return $this->dbHandler;
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
                $this->getLocationMapper(),
                $this->getFieldValueConverterRegistry(),
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
            $this->contentGateway = new Content\Gateway\EzcDatabase(
                $this->getDatabase(),
                new Content\Gateway\EzcDatabase\QueryBuilder( $this->getDatabase() ),
                $this->contentLanguageHandler(),
                $this->getLanguageMaskGenerator()
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry
     */
    public function getFieldValueConverterRegistry()
    {
        if ( !isset( $this->fieldValueConverterRegistry ) )
        {
            $this->fieldValueConverterRegistry =
                new Content\FieldValue\Converter\Registry();
            $this->configurator->configureFieldConverter(
                $this->fieldValueConverterRegistry
            );
        }
        return $this->fieldValueConverterRegistry;
    }

    /**
     * Returns the storage registry
     *
     * @return Content\StorageRegistry
     */
    public function getStorageRegistry()
    {
        if ( !isset( $this->storageRegistry ) )
        {
            $this->storageRegistry = new StorageRegistry();
            $this->configurator->configureExternalStorages(
                $this->storageRegistry
            );
        }
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
                $this->getStorageRegistry(),
                $this->getContentGateway()->getContext()
            );
        }
        return $this->storageHandler;
    }

    /**
     * Get a transformation processor for full text search normalization
     *
     * @return TransformationProcessor
     */
    protected function getTransformationProcessor()
    {
        $processor = new TransformationProcessor(
            new TransformationParser(),
            new TransformationPcreCompiler(
                new Utf8Converter()
            )
        );

        // @TODO: How do we get the path to the currently used transformation
        // files?
        $path = '.';
        foreach ( glob( $path . '/*.tr' ) as $file )
        {
            $processor->loadRules( $file );
        }

        return $processor;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function searchHandler()
    {
        if ( !isset( $this->searchHandler ) )
        {
            $db = $this->getDatabase();
            $this->searchHandler = new Content\Search\Handler(
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
                                $this->getTransformationProcessor()
                            ),
                            new CriterionHandler\Field(
                                $db,
                                $this->getFieldValueConverterRegistry()
                            ),
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
                    new Content\Gateway\EzcDatabase\QueryBuilder( $this->getDatabase() ),
                    $this->contentLanguageHandler(),
                    $this->getLanguageMaskGenerator()
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
                new TypeMapper( $this->getFieldValueConverterRegistry() ),
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
            if ( $this->configurator->shouldDeferTypeUpdates() )
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
                        $this->getFieldValueConverterRegistry(),
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
            $this->contentTypeGateway = new Type\Gateway\EzcDatabase(
                $this->getDatabase(),
                $this->getLanguageMaskGenerator()
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
                    new Content\Language\Gateway\EzcDatabase( $this->getDatabase() ),
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
            $this->locationGateway = new Content\Location\Gateway\EzcDatabase( $this->getDatabase() );
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
            $this->objectStateGateway = new Content\ObjectState\Gateway\EzcDatabase(
                $this->getDatabase(),
                $this->getLanguageMaskGenerator()
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
                new User\Gateway\EzcDatabase( $this->getDatabase() ),
                new User\Role\Gateway\EzcDatabase( $this->getDatabase() ),
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
                new Content\Section\Gateway\EzcDatabase( $this->getDatabase() )
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
                $this->getLocationGateway(),
                $this->getLocationMapper(),
                $this->contentHandler()
            );
        }

        return $this->trashHandler;
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->getDatabase()->beginTransaction();
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
            $this->getDatabase()->commit();
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
            $this->getDatabase()->rollback();
        }
        catch ( ezcDbTransactionException $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }
}
