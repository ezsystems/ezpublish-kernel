<?php
/**
 * Repository Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage base
 */

namespace ezx\base\Interfaces;
interface Repository
{
    /**
     * Constructor
     *
     * Construct repository object with provided storage engine
     *
     * @param StorageEngine $se
     */
    public function __construct( StorageEngine $se/*, \ezp\user\User $user*/ );

    /**
     * Find generic domain objects by criteria
     *
     * Retrieve generic domain objects by criteria
     *
     * @param RepositoryCriteria $criteria
     * @return array<\ezx\base\Abstracts\DomainObject>
     * @throws \InvalidArgumentException
     */
    public function find( RepositoryCriteria $criteria );

    /**
     * Get an generic object by id
     *
     * This is an alias for find() where query object to filter on id is built for you.
     * Hence it's assumed that all domain objects will have an id column.
     *
     * @param string $type
     * @param int $id
     * @return \ezx\base\Abstracts\DomainObject
     * @throws \InvalidArgumentException
     */
    public function load( $type, $id );

    /**
     * Store a generic  domain object
     *
     * Store a generic  domain object or collection of domain objects in the repository
     *
     * @param \ezx\base\Abstracts\DomainObject $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function store( \ezx\base\Abstracts\DomainObject $object );

    /**
     * Delete a generic domain object or collection of domain objects in the repository
     *
     * @param \ezx\base\Abstracts\DomainObject $object
     * @throws \DomainException If object is of wrong type
     * @throws \RuntimeException If errors occurred in storage engine
     */
    public function delete( \ezx\base\Abstracts\DomainObject $object );

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction();

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit();

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback();

    /**
     * Get Content Service
     *
     * Get service object to performe several operations on Content objects and it's aggragate memebers.
     * ( ContentLocation, ContentVersion, ContentField )
     *
     * @return \ezx\content\ContentService
     */
    public function ContentService();

    /**
     * Get Content Type Service
     *
     * Get service object to performe several operations on ContentType objects and it's aggragate memebers.
     * ( ContentTypeGroup, ContentTypeField & ContentTypeFieldCategory )
     *
     * @return \ezx\content\ContentTypeService
     */
    public function ContentTypeService();

    /**
     * Get User Service
     *
     * Get service object to performe several operations on User objects and it's aggragate memebers.
     * ( UserGroups, UserRole, UserRolePolicy & UserRolePolicyLimitation )
     *
     * @return \ezx\user\UserService
     */
    public function UserService();
}
