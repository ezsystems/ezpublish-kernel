<?php
/**
 * Repository class
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

/**
 * Repository class
 */
namespace ezx\base;
class Repository implements Interfaces\Repository
{
    /**
     * Storage Engine object
     *
     * @var Interfaces\StorageEngine
     */
    protected $se;

    /**
     * This class uses doctrine directly as backend, in BL it should talk to a
     * persistent interface
     *
     * @var User
     */
    protected $user;

    /**
     * Instances of services
     *
     * @var Interfaces\Service[]
     */
    protected $services = array();

    /**
     * Constructor
     *
     * {@inheritdoc}
     *
     * @param Interfaces\StorageEngine $se
     */
    public function __construct( Interfaces\StorageEngine $se/*, \ezp\user\User $user*/ )
    {
        $this->se = $se;
        //$this->user = $user;
    }

    /**
     * Find generic domain objects by criteria
     *
     * {@inheritdoc}
     *
     * @param Interfaces\RepositoryCriteria $criteria
     * @return Abstracts\DomainObject[]
     * @throws \InvalidArgumentException
     */
    public function find( Interfaces\RepositoryCriteria $criteria ){}

    /**
     * Get an generic object by id
     *
     * {@inheritdoc}
     *
     * @param string $type
     * @param int $id
     * @return Abstracts\DomainObject
     * @throws \InvalidArgumentException
     */
    public function load( $type, $id )
    {
        $object = $this->em->find( $type, (int) $id );
        if ( !$object )
            throw new \InvalidArgumentException( "Could not find '{$type}' with id: {$id}" );
        if ( !$object instanceof Abstracts\DomainObject )
            throw new \InvalidArgumentException( "'{$type}' is does not extend Abstracts\DomainObject" );
        if ( $object instanceof \ezx\content\Abstracts\ContentModel )
            throw new \InvalidArgumentException( "'{$type}' is a ContentModel class and is only available true Services" );
        return $object;
    }

    /**
     * Handles class for service objects, services needs to be in same namespace atm.
     *
     * @param string $className
     * @param string $handler The handler name on storage engine that corresponds to service.
     * @return Interfaces\Service
     * @throws RuntimeException
     */
    protected function service( $className, $handler )
    {
        if ( isset( $this->services[$className] ) )
            return $this->services[$className];

        if ( class_exists( $className ) )
            return $this->services[$className] = new $className( $this, $this->se->$handler() );

        throw new \RuntimeException( "Could not load '$className' service!" );
    }

    /**
     * Get Content Service
     *
     * {@inheritdoc}
     *
     * @uses service()
     * @return \ezx\content\ContentService
     */
    public function ContentService()
    {
        return $this->service( '\ezx\content\ContentService', 'ContentHandler' );
    }

    /**
     * Get Content Type Service
     *
     * {@inheritdoc}
     *
     * @uses service()
     * @return \ezx\content\ContentTypeService
     */
    public function ContentTypeService()
    {
        return $this->service( '\ezx\content\ContentTypeService', 'ContentTypeHandler' );
    }

    /**
     * Get User Service
     *
     * {@inheritdoc}
     *
     * @uses service()
     * @return \ezx\user\UserService
     */
    public function UserService()
    {
        return $this->service( '\ezx\user\UserService', 'UserHandler' );
    }


    /**
     * Store generic domain object
     *
     * {@inheritdoc}
     *
     * @param Abstracts\DomainObject $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function store( Abstracts\DomainObject $object ){}

    /**
     * Delete generic domain object
     *
     * {@inheritdoc}
     *
     * @param Abstracts\DomainObject $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function delete( Abstracts\DomainObject $object ){}

    /**
     * Begins transaction
     *
     * {@inheritdoc}
     */
    public function beginTransaction(){}

    /**
     * Commit transaction
     *
     * {@inheritdoc}
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit(){}

    /**
     * Rollback transaction
     *
     * {@inheritdoc}
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback(){}
}
