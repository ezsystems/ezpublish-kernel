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
     * This class uses doctrine directly as backend, in BL it should talk to a
     * persistent interface
     *
     * @internal
     * @var \Doctrine\ORM\EntityManager
     */
    public $em = null;

    /**
     * This class uses doctrine directly as backend, in BL it should talk to a
     * persistent interface
     *
     * @internal
     * @var User
     */
    public $user = null;

    /**
     * Instances of services
     *
     * @var array(string => Interfaces\Service)
     */
    protected $services = array();

    /**
     * @var Repository
     */
    protected static $instance = null;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct( /*User $user,*/ \Doctrine\ORM\EntityManager $em )
    {
        $this->em = $em;
        //$this->user = $user;
    }

    /**
     * Get instance
     *
     * @return Repository
     * @throw \RuntimeException
     */
    public static function get()
    {
        if ( self::$instance === null )
            throw new \RuntimeException( "Instance has not been set using set()" );
        return self::$instance;
    }

    /**
     * Set instance
     *
     * @param Repository $instance
     * return Repository
     */
    public static function set( Repository $instance )
    {
        return self::$instance = $instance;
    }

    /**
     * Retrive objects by criteria
     *
     * @param Interfaces\RepositoryCriteria $criteria
     * @return array<object>
     * @throws \InvalidArgumentException
     */
    public function find( Interfaces\RepositoryCriteria $criteria ){}

    /**
     * Get an object by id
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
     * @return Interfaces\Service
     * @throws RuntimeException
     */
    protected function service( $className )
    {
        if ( isset( $this->services[$className] ) )
            return $this->services[$className];

        if ( class_exists( $className ) )
            return $this->services[$className] = new $className( $this );

        throw new \RuntimeException( "Could not load '$className' service!" );
    }

    /**
     * Get Content Service
     *
     * @uses service()
     * @return \ezx\content\ContentService
     */
    function ContentService()
    {
        return $this->service( '\ezx\content\ContentService' );
    }

    /**
     * Get Content Service
     *
     * @uses service()
     * @return \ezx\content\ContentTypeService
     */
    function ContentTypeService()
    {
        return $this->service( '\ezx\content\ContentTypeService' );
    }

    /**
     * Get User Service
     *
     * @uses service()
     * @return \ezx\user\UserService
     */
    function UserService()
    {
        return $this->service( '\ezx\user\UserService' );
    }


    /**
     * Store a domain object or collection of domain objects in the repository
     *
     * @param Abstracts\DomainObject $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function store( Abstracts\DomainObject $object ){}

    /**
     * Delete a domain object or collection of domain objects in the repository
     *
     * @param Abstracts\DomainObject $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function delete( Abstracts\DomainObject $object ){}

    /**
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction(){}

    /**
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit(){}

    /**
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback(){}
}
