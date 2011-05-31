<?php
/**
 * A mock Abstract Repository
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Mock Abstract Repository
 */
namespace ezx\doctrine\model;
abstract class Abstract_Repository implements Interface_Repository
{
    /**
     * This class uses doctrine directly as backend, in BL it should talk to a
     * persistent interface
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em = null;

    /**
     * @var ContentRepository
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
     * @return ContentRepository
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
     * @param ContentRepository $instance
     */
    public static function set( ContentRepository $instance )
    {
        self::$instance = $instance;
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
     * @return object
     * @throws \InvalidArgumentException
     */
    public function load( $type, $id )
    {
        $contentType = $this->em->find( "ezx\doctrine\model\\{$type}", (int) $id );
        if ( !$contentType )
            throw new \InvalidArgumentException( "Could not find '{$type}' with id: {$id}" );
        return $contentType;
    }

    /**
     * Get an object by identifier
     *
     * @param string $type
     * @param string $identifier
     * @return object
     * @throws \InvalidArgumentException
     */
    public function loadByIdentifier( $type, $identifier )
    {
        $query = $this->em->createQuery( "SELECT a FROM ezx\doctrine\model\\{$type} a WHERE a.identifier = :identifier" );
        $query->setParameter( 'identifier', $identifier );
        $contentType = $query->getResult();
        if ( !$contentType )
            throw new \InvalidArgumentException( "Could not find '{$type}' with identifier: {$identifier}" );
        return $contentType;
    }

    /**
     * Store a model or collection of models in the repository
     *
     * @param ModelCollectionInterface|ModelInterface $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function store( object $object ){}

    /**
     * Delete a model or collection of models in the repository
     *
     * @param ModelCollectionInterface|ModelInterface $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function delete( object $object ){}

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
