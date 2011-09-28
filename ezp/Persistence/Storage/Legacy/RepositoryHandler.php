<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy;
use ezp\Persistence\Repository\Handler as HandlerInterface,
    ezp\Persistence\Storage\Legacy\Content,
    ezp\Persistence\Storage\Legacy\Content\Type,
    ezp\Persistence\Storage\Legacy\Content\Location\Handler as LocationHandler,
    ezp\Persistence\Storage\Legacy\User;

/**
 * The repository handler for the legacy storage engine
 *
 * @todo If possible, the handler should not receive the DSN but the database
 *       connection instead, so that the implementation becomes fully testable.
 */
class RepositoryHandler implements HandlerInterface
{
    /**
     * Content handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Field value converter registry
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $fieldValueConverterRegistry;

    /**
     * Storage registry
     *
     * @var Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Search handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * Content type handler
     *
     * @var Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Content Type gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected $contentTypeGateway;

    /**
     * Content Type update handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler
     */
    protected $typeUpdateHandler;

    /**
     * Location handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Location gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location mapper
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * User handler
     *
     * @var User\Handler
     */
    protected $userHandler;

    /**
     * Section handler
     *
     * @var mixed
     */
    protected $sectionHandler;

    /**
     * Content gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Language handler
     *
     * @var \ezp\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Language cache
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Cache
     */
    protected $languageCache;

    /**
     * Language mask generator
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Configurator
     *
     * @var \ezp\Persistence\Storage\Legacy\Configurator
     */
    protected $configurator;

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
     * For further information on the database setup, please refer to
     * {@see http://incubator.apache.org/zetacomponents/documentation/trunk/Database/tutorial.html#handler-usage}
     *
     * The flag 'defer_type_update' defines if content types should be
     * published immediatly (false), when the
     * {@link \ezp\Persistence\Content\Type\Handler::publish()} method is
     * called, or if a background process should be triggered (true), which is
     * then executed by the old eZ Publish core.
     *
     * In 'external_storages' a mapping of field type names to classes is
     * expected. The referred class is instantiated and the resulting object is 
     * used to store/restore/delete/… data in external storages (e.g. another
     * database or a web service). The classes must comply to the
     * {@link \ezp\Persistence\Fields\Storage} interface. Note that due to the
     * configuration mechanism and missing proper DI, the classes may not
     * expect any constructor parameters!
     *
     * The 'field_converter' configuration array consists of another mapping of 
     * field type names to classes. Each of the classes is instantiated and
     * used to convert content fields and content type field definitions to the 
     * legacy storage engine. The given class names must derive the
     * {@link \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter}
     * class. As for 'external_storage' classes, none of the classes may expect 
     * parameters in its constructor, due to missing proper DI.
     *
     * Through the 'transformation_rule_files' array, a list of files with
     * full text transformation rules is given. These files are read by an
     * instance of
     * {@link \ezp\Persistence\Storage\Legacy\Converter\Search\TransformationProcessor}
     * and then used for normalization in the full text search.
     *
     * @param array $config
     */
    public function __construct( array $config )
    {
        $this->configurator = new Configurator( $config );
    }

    /**
     * Returns the Zeta Database handler
     *
     * @return \ezp\Persistence\Storage\Legacy\EzcDbHandler
     */
    protected function getDatabase()
    {
        if ( !isset( $this->dbHandler ) )
        {
            $connection = \ezcDbFactory::create( $this->configurator->getDsn() );
            $database   = preg_replace( '(^([a-z]+).*)', '\\1', $this->configurator->getDsn() );

            switch ( $database )
            {
                case 'pgsql':
                    $this->dbHandler = new EzcDbHandler\Pgsql( $connection );
                    break;

                case 'sqlite':
                    $this->dbHandler = new EzcDbHandler\Sqlite( $connection );
                    break;

                default:
                    $this->dbHandler = new EzcDbHandler( $connection );
            }
        }
        return $this->dbHandler;
    }

    /**
     * @return \ezp\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new Content\Handler(
                $this->getContentGateway(),
                $this->getLocationGateway(),
                $this->getContentTypeGateway(),
                new Content\Mapper(
                    $this->getLocationMapper(),
                    $this->getFieldValueConverterRegistry()
                ),
                $this->getStorageRegistry()
            );
        }
        return $this->contentHandler;
    }

    /**
     * Returns a content gateway
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
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
     * Returns a language mask generator
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\MaskGenerator
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
     * @return \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
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
            $this->storageRegistry = new Content\StorageRegistry();
            $this->configurator->configureExternalStorages(
                $this->storageRegistry
            );
        }
        return $this->storageRegistry;
    }

    /**
     * Get a transformation processor for full text search normalization
     *
     * @return TransformationProcessor
     */
    protected function getTransformationProcessor()
    {
        $processor = new Content\Search\TransformationProcessor(
            new Content\Search\TransformationParser(),
            new Content\Search\TransformationPcreCompiler(
                new Content\Search\Utf8Converter()
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
     * @return \ezp\Persistence\Content\Search\Handler
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
                            new Content\Search\Gateway\CriterionHandler\ContentId( $db ),
                            new Content\Search\Gateway\CriterionHandler\LogicalNot( $db ),
                            new Content\Search\Gateway\CriterionHandler\LogicalAnd( $db ),
                            new Content\Search\Gateway\CriterionHandler\LogicalOr( $db ),
                            new Content\Search\Gateway\CriterionHandler\Subtree( $db ),
                            new Content\Search\Gateway\CriterionHandler\ContentTypeId( $db ),
                            new Content\Search\Gateway\CriterionHandler\ContentTypeGroupId( $db ),
                            new Content\Search\Gateway\CriterionHandler\DateMetadata( $db ),
                            new Content\Search\Gateway\CriterionHandler\LocationId( $db ),
                            new Content\Search\Gateway\CriterionHandler\ParentLocationId( $db ),
                            new Content\Search\Gateway\CriterionHandler\RemoteId( $db ),
                            new Content\Search\Gateway\CriterionHandler\SectionId( $db ),
                            new Content\Search\Gateway\CriterionHandler\Status( $db ),
                            new Content\Search\Gateway\CriterionHandler\FullText(
                                $db,
                                $this->getTransformationProcessor()
                            ),
                            new Content\Search\Gateway\CriterionHandler\Field(
                                $db,
                                $this->getFieldValueConverterRegistry()
                            ),
                        )
                    ),
                    new Content\Search\Gateway\SortClauseConverter(
                        array(
                            new Content\Search\Gateway\SortClauseHandler\LocationPathString( $db ),
                            new Content\Search\Gateway\SortClauseHandler\LocationDepth( $db ),
                            new Content\Search\Gateway\SortClauseHandler\LocationPriority( $db ),
                        )
                    ),
                    new Content\Gateway\EzcDatabase\QueryBuilder( $this->getDatabase() )
                ),
                new Content\Mapper(
                    new Content\Location\Mapper(),
                    $this->getFieldValueConverterRegistry()
                )
            );
        }
        return $this->searchHandler;
    }

    /**
     * @return \ezp\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        if ( !isset( $this->contentTypeHandler ) )
        {
            $this->contentTypeHandler = new Type\Handler(
                $this->getContentTypeGateway(),
                new Type\Mapper( $this->getFieldValueConverterRegistry() ),
                $this->getTypeUpdateHandler()
            );
        }
        return $this->contentTypeHandler;
    }

    /**
     * Returns a Content Type update handler
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler
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
                        $this->getFieldValueConverterRegistry()
                    )
                );
            }
        }
        return $this->typeUpdateHandler;
    }

    /**
     * Returns the content type gateway
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected function getContentTypeGateway()
    {
        if ( !isset( $this->contentTypeGateway ) )
        {
            $this->contentTypeGateway = new Content\Type\Gateway\EzcDatabase(
                $this->getDatabase(),
                $this->getLanguageMaskGenerator()
            );
        }
        return $this->contentTypeGateway;
    }

    /**
     * @return \ezp\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $this->languageHandler = new Content\Language\CachingHandler(
                new Content\Language\Handler(
                    new Content\Language\Gateway\EzcDatabase( $this->getDatabase() ),
                    new Content\Language\Mapper()
                ),
                $this->getLanguageCache()
            );
        }
        return $this->languageHandler;
    }

    /**
     * Returns a Language cache
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\Cache
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
     * @return \ezp\Persistence\Content\Location\Handler
     */
    public function locationHandler()
    {
        if ( !isset( $this->locationHandler ) )
        {
            $this->locationHandler = new LocationHandler(
                $this->getLocationGateway(),
                $this->getLocationMapper()
            );
        }
        return $this->locationHandler;
    }

    /**
     * Returns a location gateway
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Location\Gateway\EzcDatabase
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
     * @return \ezp\Persistence\Storage\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapper()
    {
        if ( !isset( $this->locationMapper ) )
        {
            $this->locationMapper = new Content\Location\Mapper();
        }
        return $this->locationMapper;
    }

    /**
     * @return \ezp\Persistence\User\Handler
     */
    public function userHandler()
    {
        if ( !isset( $this->userHandler ) )
        {
            $this->userHandler = new User\Handler(
                new User\Gateway\EzcDatabase( $this->getDatabase() ),
                new User\Role\Gateway\EzcDatabase( $this->getDatabase() ),
                new User\Mapper()
            );
        }
        return $this->userHandler;
    }

    /**
     * @return \ezp\Persistence\Content\Section\Handler
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
     * @return \ezp\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler()
    {
        throw new \RuntimeException( 'Not implemented yet' );
    }

    /**
     */
    public function beginTransaction()
    {
        $this->getDatabase()->beginTransaction();
    }

    /**
     */
    public function commit()
    {
        $this->getDatabase()->commit();
    }

    /**
     */
    public function rollback()
    {
        $this->getDatabase()->rollback();
    }
}
?>
