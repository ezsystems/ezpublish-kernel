<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Repository class
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\Base;
class Repository
{
    /**
     * Repository Handler object
     *
     * @var \ezp\Persistence\Interfaces\RepositoryHandler
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
     * @var AbstractService[]
     */
    protected $services = array();

    /**
     * Constructor
     *
     * Construct repository object with provided storage engine
     *
     * @param \ezp\Persistence\Interfaces\RepositoryHandler $handler
     */
    public function __construct( \ezp\Persistence\Interfaces\RepositoryHandler $handler/*, \ezp\User\User $user*/ )
    {
        $this->handler = $handler;
        //$this->user = $user;
    }

    /**
     * Handles class loading for service objects
     *
     * @param string $className
     * @return AbstractService
     * @throws \RuntimeException
     */
    protected function service( $className )
    {
        if ( isset( $this->services[$className] ) )
            return $this->services[$className];

        if ( class_exists( $className ) )
            return $this->services[$className] = new $className( $this, $this->handler );

        throw new \RuntimeException( "Could not load '$className' service!" );
    }

    /**
     * Get Content Service
     *
     * Get service object to perform several operations on Content objects and it's aggregate members.
     * ( ContentLocation, ContentVersion, ContentField )
     *
     * @return \ezp\Content\Services\Content
     */
    public function getContentService()
    {
        return $this->service( '\ezp\Content\Services\Content' );
    }

    /**
     * Get Content Type Service
     *
     * Get service object to perform several operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \ezp\Content\Services\ContentType
     */
    public function getContentTypeService()
    {
        return $this->service( '\ezp\Content\Services\ContentType' );
    }

    /**
     * Get Content Service
     *
     * Get service object to perform several operations on Content objects and it's aggregate members.
     * ( ContentLocation, ContentVersion, ContentField )
     *
     * @return \ezp\Content\Services\Location
     */
    public function getLocationService()
    {
        return $this->service( '\ezp\Content\Services\Location' );
    }

    /**
     * Get User Service
     *
     *
     * @return \ezp\Content\Services\Section
     */
    public function getSectionService()
    {
        return $this->service( '\ezp\Content\Services\Section' );
    }

    /**
     * Get User Service
     *
     * Get service object to perform several operations on User objects and it's aggregate members.
     * ( UserGroups, UserRole, UserRolePolicy & UserRolePolicyLimitation )
     *
     * @return \ezp\User\UserService
     */
    public function getUserService()
    {
        return $this->service( '\ezp\User\UserService' );
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
     * @throws \RuntimeException If no transaction has been started
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
     * @throws \RuntimeException If no transaction has been started
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
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function store( AbstractModel $object )
    {
    }

    /**
     * Delete a generic domain object or collection of domain objects in the repository
     *
     * @internal
     * @param AbstractModel $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
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
