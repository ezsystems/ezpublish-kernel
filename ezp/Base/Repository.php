<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Persistence\Repository\Handler,
    RuntimeException,
    DomainException;

/**
 * Repository class
 *
 */
class Repository
{
    /**
     * Repository Handler object
     *
     * @var ezp\Persistence\Repository\Handler
     */
    protected $handler;

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
     * @var Service[]
     */
    protected $services = array();

    /**
     * Constructor
     *
     * Construct repository object with provided storage engine
     *
     * @param ezp\Persistence\Repository\Handler $handler
     */
    public function __construct( Handler $handler/*, ezp\User $user*/ )
    {
        $this->handler = $handler;
        //$this->user = $user;
    }

    /**
     * Handles class loading for service objects
     *
     * @param string $className
     * @return Service
     * @throws RuntimeException
     */
    protected function service( $className )
    {
        if ( isset( $this->services[$className] ) )
            return $this->services[$className];

        if ( class_exists( $className ) )
            return $this->services[$className] = new $className( $this, $this->handler );

        throw new RuntimeException( "Could not load '$className' service!" );
    }

    /**
     * Get Content Service
     *
     * Get service object to perform several operations on Content objects and it's aggregate members.
     * ( ContentLocation, ContentVersion, ContentField )
     *
     * @return ezp\Content\Service
     */
    public function getContentService()
    {
        return $this->service( 'ezp\\Content\\Service' );
    }

    /**
     * Get Content Type Service
     *
     * Get service object to perform several operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return ezp\Content\Type\Service
     */
    public function getContentTypeService()
    {
        return $this->service( 'ezp\\Content\\Type\\Service' );
    }

    /**
     * Get Content Service
     *
     * Get service object to perform several operations on Content objects and it's aggregate members.
     * ( ContentLocation, ContentVersion, ContentField )
     *
     * @return ezp\Content\Location\Service
     */
    public function getLocationService()
    {
        return $this->service( 'ezp\\Content\\Location\\Service' );
    }

    /**
     * Get User Service
     *
     *
     * @return ezp\Content\Section\Service
     */
    public function getSectionService()
    {
        return $this->service( 'ezp\\Content\\Section\\Service' );
    }

    /**
     * Get User Service
     *
     * Get service object to perform several operations on User objects and it's aggregate members.
     * ( UserGroups, UserRole, UserRolePolicy & UserRolePolicyLimitation )
     *
     * @return ezp\User\Service
     */
    public function getUserService()
    {
        return $this->service( 'ezp\\User\\Service' );
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->handler->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws RuntimeException If no transaction has been started
     */
    public function commit()
    {
        $this->handler->commit();
    }

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        $this->handler->rollback();
    }

    /**
     * Store a generic domain object
     *
     * Store a generic domain object or collection of domain objects in the repository
     *
     * @internal
     * @param AbstractModel $object
     * @throws DomainException If object is of wrong type
     * @throws RuntimeException If errors occurred in storage engine
     */
    public function store( AbstractModel $object )
    {
    }

    /**
     * Delete a generic domain object or collection of domain objects in the repository
     *
     * @internal
     * @param AbstractModel $object
     * @throws DomainException If object is of wrong type
     * @throws RuntimeException If errors occurred in storage engine
     */
    public function delete( AbstractModel $object )
    {
    }

    /**
     * Find generic domain objects by criteria
     *
     * Retrieve generic domain objects by criteria
     *
     * @internal
     * @param RepositoryCriteriaInterface $criteria
     * @return AbstractModel[]
     * @throws \InvalidArgumentException
     */
    public function find( RepositoryCriteriaInterface $criteria )
    {
    }

    /**
     * Get an generic object by id
     *
     * This is an alias for find() where query object to filter on id is built for you.
     * Hence it's assumed that all domain objects will have an id column.
     *
     * @internal
     * @param string $type
     * @param int $id
     * @return AbstractModel
     * @throws \InvalidArgumentException
     */
    public function load( $type, $id )
    {
    }
}
