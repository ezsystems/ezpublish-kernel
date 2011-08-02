<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage;
use ezp\Persistence\Interfaces,
    ezp\Persistence\LegacyStorage\Content,
    ezp\Persistence\LegacyStorage\Content\Type,
    ezp\Persistence\LegacyStorage\User;

/**
 * The repository handler for the legacy storage engine
 *
 * @todo If possible, the handler should not receive the DSN but the database
 *       connection instead, so that the implementation becomes fully testable.
 */
class RepositoryHandler implements Interfaces\RepositoryHandler
{
    /**
     * Content handler
     *
     * @var Content\ContentHandler
     */
    protected $contentHandler;

    /**
     * Field value converter registry
     *
     * @var Content\FieldValueConverterRegistry
     */
    protected $fieldValueConverterRegistry;

    /**
     * Storage registry
     *
     * @var Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Content type handler
     *
     * @var Content\Type\ContentTypeHandler
     */
    protected $contentTypeHandler;

    /**
     * Location handler
     *
     * @var Content\LocationHandler
     */
    protected $locationHandler;

    /**
     * User handler
     *
     * @var User\UserHandler
     */
    protected $userHandler;

    /**
     * Section handler
     *
     * @var mixed
     */
    protected $sectionHandler;

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
        $this->dbHandler = \ezcDbFactory::create( $dsn );
    }

    /**
     * @return \ezp\Persistence\Content\Interfaces\ContentHandler
     */
    public function contentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new Content\ContentHandler(
                new Content\ContentGateway\EzcDatabase( $this->dbHandler ),
                new Content\Mapper( $this->getFieldValueConverterRegistry() ),
                new Content\StorageRegistry( $this->getStorageRegistry() )
            );
        }
        return $this->contentHandler;
    }

    /**
     * Returns the field value converter registry
     *
     * @return Content\FieldValueConverterRegistry
     */
    public function getFieldValueConverterRegistry()
    {
        if ( !isset( $this->fieldValueConverterRegistry ) )
        {
            $this->fieldValueConverterRegistry =
                new Content\FieldValueConverterRegistry();
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
     * @return \ezp\Persistence\Content\Type\Interfaces\Handler
     */
    public function contentTypeHandler()
    {
        if ( !isset( $this->contentTypeHandler ) )
        {
            $this->contentTypeHandler = new Type\ContentTypeHandler(
                new Type\ContentTypeGateway\EzcDatabase( $this->dbHandler ),
                new Type\Mapper()
            );
        }
        return $this->contentTypeHandler;
    }

    /**
     * @return \ezp\Persistence\Content\Interfaces\LocationHandler
     */
    public function locationHandler()
    {
        if ( !isset( $this->locationHandler ) )
        {
            $this->locationHandler = new Content\LocationHandler(
                $this->contentHandler(),
                new Content\LocationGateway\EzcDatabase( $this->dbHandler )
            );
        }
        return $this->locationHandler;
    }

    /**
     * @return \ezp\Persistence\User\Interfaces\UserHandler
     */
    public function userHandler()
    {
        if ( !isset( $this->userHandler ) )
        {
            $this->userHandler = new User\UserHandler(
                new User\UserGateway\EzcDatabase( $this->dbHandler ),
                new User\RoleGateway\EzcDatabase( $this->dbHandler )
            );
        }
        return $this->userHandler;
    }

    /**
     * @return \ezp\Persistence\Content\Interfaces\SectionHandler
     */
    public function sectionHandler()
    {
        throw new RuntimeException( 'Not implemented, yet.' );
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
