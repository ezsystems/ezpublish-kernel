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
    ezp\Persistence\Storage\Legacy\User,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry;

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
     * Location handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Handler
     */
    protected $locationHandler;

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
     * Creates a new repository handler.
     *
     * The $dsn is a data source name as expected by the Zeta Components
     * database component. The format is:
     *
     *     <database>://<user>:<password>@<host>/<database>
     *
     * For example
     *
     *     mysql://root:secret@localhost/ezp
     *
     * for a MySQL database connection or
     *
     *    sqlite://:memory:
     *
     * for an SQLite in-memory database.
     *
     * For further information refer to
     * {@see http://incubator.apache.org/zetacomponents/documentation/trunk/Database/tutorial.html#handler-usage}
     *
     * @param string $dsn
     */
    public function __construct( $dsn )
    {
        $this->dbHandler = new EzcDbHandler( \ezcDbFactory::create( $dsn ) );
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
                $this->locationHandler(),
                new Content\Mapper(
                    $this->getLocationMapper(),
                    $this->getFieldValueConverterRegistry()
                ),
                new Content\StorageRegistry( $this->getStorageRegistry() )
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
                $this->dbHandler,
                new Content\Gateway\EzcDatabase\QueryBuilder( $this->dbHandler )
            );
        }
        return $this->contentGateway;
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
                new Registry();
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

            $this->searchHandler = new Content\Search\Handler(
                new Content\Search\Gateway\EzcDatabase(
                    $this->dbHandler,
                    new Content\Search\Gateway\CriteriaConverter(
                        array(
                            new Content\Search\Gateway\CriterionHandler\ContentId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\LogicalNot(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\LogicalAnd(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\LogicalOr(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\SubtreeId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\ContentTypeId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\ContentTypeGroupId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\DateMetadata(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\LocationId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\ParentLocationId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\RemoteId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\SectionId(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\Status(
                                $this->dbHandler
                            ),
                            new Content\Search\Gateway\CriterionHandler\FullText(
                                $this->dbHandler,
                                $this->getTransformationProcessor()
                            ),
                            new Content\Search\Gateway\CriterionHandler\Field(
                                $this->dbHandler,
                                $this->getFieldValueConverterRegistry()
                            ),
                        )
                    ),
                    new Content\Gateway\EzcDatabase\QueryBuilder( $this->dbHandler )
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
                new Type\Gateway\EzcDatabase(
                    $this->dbHandler,
                    new Content\Language\MaskGenerator( $this->getLanguageCache() )
                ),
                new Type\Mapper( $this->getFieldValueConverterRegistry() ),
                new Type\ContentUpdater(
                    $this->searchHandler(),
                    $this->getContentGateway(),
                    $this->getFieldValueConverterRegistry()
                )
            );
        }
        return $this->contentTypeHandler;
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
                    new Content\Language\Gateway\EzcDatabase( $this->dbHandler ),
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
                new Content\Location\Gateway\EzcDatabase( $this->dbHandler ),
                $this->getLocationMapper()
            );
        }
        return $this->locationHandler;
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
                new User\Gateway\EzcDatabase( $this->dbHandler ),
                new User\Role\Gateway\EzcDatabase( $this->dbHandler )
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
                new Content\Section\Gateway\EzcDatabase( $this->dbHandler )
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
        $this->dbHandler->beginTransaction();
    }

    /**
     */
    public function commit()
    {
        $this->dbHandler->commit();
    }

    /**
     */
    public function rollback()
    {
        $this->dbHandler->rollback();
    }
}
?>
