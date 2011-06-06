<?php
/**
 * Repository class
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Repository class
 */
namespace ezx\doctrine\model;
class Repository implements Interface_Repository
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
     * Instances of services
     * @var array(string => Interface_Service)
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
    public function __construct( \Doctrine\ORM\EntityManager $em )
    {
        $this->em = $em;
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
     * @param Interface_RepositoryCriteria $criteria
     * @return array<object>
     * @throws \InvalidArgumentException
     */
    public function find( Interface_RepositoryCriteria $criteria ){}

    /**
     * Get an object by id
     *
     * {@inheritdoc}
     *
     * @param string $type
     * @param int $id
     * @return Abstract_Model
     * @throws \InvalidArgumentException
     */
    public function load( $type, $id )
    {
        $object = $this->em->find( "ezx\doctrine\model\\{$type}", (int) $id );
        if ( !$object )
            throw new \InvalidArgumentException( "Could not find '{$type}' with id: {$id}" );
        if ( !$object instanceof Abstract_Model )
            throw new \InvalidArgumentException( "'{$type}' is does not extend Abstract_Model" );
        return $object;
    }

    /**
     * Store a model or collection of models in the repository
     *
     * @param Abstract_Model $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function store( Abstract_Model $object ){}

    /**
     * Delete a model or collection of models in the repository
     *
     * @param Abstract_Model $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function delete( Abstract_Model $object ){}

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


    /**
     * Handles class for service objects, services needs to be in same namespace atm.
     *
     * @param string $name
     * @param array $arguments
     * @return Interface_Service
     */
    function __call ( $name, array $arguments )
    {
        if ( isset( $this->services[$name] ) )
            return $this->services[$name];
        $name = __NAMESPACE__ . '\\' . $name;
        if ( class_exists( $name ) )
            return $this->services[$name] = new $name( $this );

        throw new \RuntimeException( "Could not load '$name' service!" );
    }
}
