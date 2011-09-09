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
    ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\Logic,
    ezp\Base\ModelDefinition,
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
            $this->setUser( $user );
        else
            $this->user = new Proxy(
                $this->getUserService(),
                Configuration::getInstance( 'site' )->get( 'UserSettings', 'AnonymousUserID', 10 )
            );

    }

    /**
     * Get current user
     *
     * @return \ezp\User
     */
    function getUser()
    {
        if ( $this->user instanceof ProxyInterface )
            $this->user = $this->user->load();

        return $this->user;
    }

    /**
     * Set current user
     *
     * @param \ezp\User $user
     * @throws \ezp\Base\Exception\InvalidArgumentValue If provided user does not have a valid id value
     * @todo throw something if $user is not persisted to backend (not stored)
     */
    function setUser( User $user )
    {
        if ( !$user->id )
            throw new InvalidArgumentValue( '$user->id', $user->id );

        $this->user = $user;
    }

    /**
     * Check if current user has access to a certain function on a model
     *
     * @param string $function Eg: read, move, create
     * @param \ezp\Base\ModelDefinition $module An model instance
     * @param \ezp\Base\Model $assignment An additional model instance in cases like 'assign' and so on
     * @return bool
     * @throws \ezp\Base\Exception\InvalidArgumentValue On invalid $function value
     * @throws \ezp\Base\Exception\BadConfiguration On missing __module__ in $model::defintion()
     * @throws \ezp\Base\Exception\Logic On limitation used in policies but not in $model::defintion()
     */
    public function canUser( $function, ModelDefinition $model, Model $assignment = null )
    {
        $definition = $model->definition();
        $className = get_class( $model );

        if ( !isset( $definition['module'] ) )
        {
            throw new BadConfiguration( "{$className}::definition()", 'missing module key with name of module' );
        }
        else if ( !empty( $definition['functions'] ) && !isset( $definition['functions'][$function] ) )
        {
            throw new InvalidArgumentValue( '$function', $function, $className );
        }

        $limitations = $this->getUser()->hasAccessTo( $definition['module'], $function );
        if ( $limitations === false || $limitations === true )
        {
            return $limitations;
        }
        else if ( empty( $definition['functions'][$function] ) )
        {
            throw new BadConfiguration(
                "{$className}::definition()",
                "function limitations returned for '{$function}', but none defined in definition()"
            );
        }

        foreach ( $limitations as $limitationKey => $limitationValues )
        {
            //if ( isset( $definition[$function][$limitationKey]['alias'] ) )
                //$limitationKey = $definition[$function][$limitationKey]['alias'];

            if ( !isset( $definition['functions'][$function][$limitationKey]['compare'] ) )
            {
                throw new Logic(
                    "\$definition[functions][{$function}][{$limitationKey}][compare]",
                    "could not find limitation compare function on {$className}::definition()"
                );
            }

            $limitationCompareFn = $definition['functions'][$function][$limitationKey]['compare'];
            if ( !is_callable( $limitationCompareFn ) )
            {
                throw new Logic(
                    "\$definition[functions][{$function}][{$limitationKey}][compare]",
                    "compare function from {$className}::definition() is not callable"
                );
            }

            if ( !$limitationCompareFn( $model, $limitationValues, $this, $assignment ) )
                return false;
        }
        return true;
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
