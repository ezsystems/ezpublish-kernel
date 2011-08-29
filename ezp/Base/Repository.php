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
    DomainException,
    ezp\Base\Configuration,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Proxy,
    ezp\Base\ProxyInterface,
    ezp\User;

/**
 * Repository class
 *
 */
class Repository
{
    /**
     * Repository Handler object
     *
     * @var \ezp\Persistence\Repository\Handler
     */
    protected $handler;

    /**
     * Currently logged in user object for permission purposes
     *
     * @var \ezp\User|\ezp\Base\ProxyInterface
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
     * @param \ezp\Persistence\Repository\Handler $handler
     * @param \ezp\User|null $user
     */
    public function __construct( Handler $handler, User $user = null )
    {
        $this->handler = $handler;

        if ( $user !== null )
            $this->setCurrentUser( $user );
        else
            $this->user = new Proxy( $this->getUserService(),
                                     Configuration::getInstance( 'site' )->get( 'UserSettings', 'AnonymousUserID', 10 )  );

    }

    /**
     * Get currently logged in user
     *
     * @return \ezp\User
     */
    function getCurrentUser()
    {
        if ( $this->user instanceof ProxyInterface )
            $this->user = $this->user->load();

        return $this->user;
    }

    /**
     * Set currently logged in user
     *
     * @param \ezp\User $user
     * @throws \ezp\Base\Exception\InvalidArgumentValue If provided user does not have a valid id value
     * @todo throw something if $user is not persisted to backend (not stored)
     */
    function setCurrentUser( User $user )
    {
        if ( !$user->id )
            throw new InvalidArgumentValue( '$user->id', $user->id );

        $this->user = $user;
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
     * @return \ezp\Content\Service
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
     * @return \ezp\Content\Type\Service
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
     * @return \ezp\Content\Location\Service
     */
    public function getLocationService()
    {
        return $this->service( 'ezp\\Content\\Location\\Service' );
    }

    /**
     * Get Trash service
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return type \ezp\Content\Location\Trash\Service
     */
    public function getTrashService()
    {
        return $this->service( 'ezp\\Content\\Location\\Trash\\Service' );
    }

    /**
     * Get User Service
     *
     *
     * @return \ezp\Content\Section\Service
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
     * @return \ezp\User\Service
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
}
