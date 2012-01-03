<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Persistence\Handler as PersistenceHandler,
    RuntimeException,
    DomainException,
    ezp\Base\Configuration,
    ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\Logic,
    ezp\Base\ModelDefinition,
    ezp\Base\ModelState,
    ezp\Io\Handler as IoHandler,
    ezp\User,
    ezp\User\Proxy as ProxyUser;

/**
 * Repository class
 *
 */
class Repository
{
    /**
     * Repository Handler object
     *
     * @var \ezp\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Io Handler object
     *
     * @var \ezp\Io\Handler
     */
    protected $ioHandler;

    /**
     * Currently logged in user object for permission purposes
     *
     * @var \ezp\User
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
     * @param \ezp\Persistence\Handler $handler
     * @param \ezp\Io\Handler $ioHandler
     * @param \ezp\User|null $user
     */
    public function __construct( PersistenceHandler $persistenceHandler, IoHandler $ioHandler, User $user = null )
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->ioHandler = $ioHandler;

        if ( $user !== null )
            $this->setUser( $user );
        else
            $this->user = new ProxyUser(
                Configuration::getInstance( 'site' )->get( 'UserSettings', 'AnonymousUserID', 10 ),
                $this->getUserService()
            );

    }

    /**
     * Get current user
     *
     * @return \ezp\User
     */
    function getUser()
    {
        return $this->user;
    }

    /**
     * Set current user
     *
     * @param \ezp\User $user
     * @throws \ezp\Base\Exception\InvalidArgumentValue If provided user does not have a valid id value
     * @todo throw something if $user is not persisted to backend (not stored)
     * @return \ezp\User Old user
     */
    function setUser( User $user )
    {
        if ( !$user->id )
            throw new InvalidArgumentValue( '$user->id', $user->id );

        $oldUser = $this->user;
        $this->user = $user;
        return $oldUser;
    }

    /**
     * Check if current user has access to a certain function on a model
     *
     * @param string $function Eg: read, move, create
     * @param \ezp\Base\ModelDefinition $model An model instance
     * @param \ezp\Base\ModelState $assignment An additional model instance in cases like 'assign' and so on
     * @param array $deniedBy Optional array by reference that will contain limitations that denied access for debug use
     * @return bool
     * @throws \ezp\Base\Exception\InvalidArgumentValue On invalid $function value
     * @throws \ezp\Base\Exception\BadConfiguration On missing __module__ in $model::defintion()
     * @throws \ezp\Base\Exception\Logic On limitation used in policies but not in $model::defintion()
     */
    public function canUser( $function, ModelDefinition $model, ModelState $assignment = null, &$deniedBy = null )
    {
        $definition = $model->definition();
        $className = get_class( $model );

        if ( !isset( $definition['module'] ) )
        {
            throw new BadConfiguration( "{$className}::definition()", 'missing module key with name of module' );
        }
        if ( !empty( $definition['functions'] ) && !isset( $definition['functions'][$function] ) )
        {
            throw new InvalidArgumentValue( '$function', $function, $className );
        }

        $limitationArray = $this->getUser()->hasAccessTo( $definition['module'], $function );
        if ( $limitationArray === false || $limitationArray === true )
        {
            return $limitationArray;
        }
        if ( empty( $definition['functions'][$function] ) )
        {
            throw new BadConfiguration(
                "{$className}::definition()",
                "function limitations returned for '{$function}', but none defined in definition()"
            );
        }

        foreach ( $limitationArray as $limitationSet )
        {
            $limitationSetSaysYes = true;
            foreach ( $limitationSet as $limitationKey => $limitationValues )
            {
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
                {
                    $limitationSetSaysYes = false;
                    // Break to next limitationSet unless $deniedBy is used
                    if ( $deniedBy === null )
                        break;
                    else
                        $deniedBy[] = array( 'limitation' => $limitationKey, 'values' => $limitationValues );
                }
            }
            if ( $limitationSetSaysYes )
                return true;
        }
        return false;
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
        {
            // @todo Use Service Container? But then it needs to be a dependency though
            if ( strpos( $className, 'ezp\\Io\\' ) === 0 )
                return $this->services[$className] = new $className( $this, $this->ioHandler );

            return $this->services[$className] = new $className( $this, $this->persistenceHandler );
        }

        throw new RuntimeException( "Could not load '$className' service!" );
    }

    /**
     * Get Content Service
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     * ( ContentLocation, ContentVersion, ContentField )
     *
     * @return \ezp\Content\Service
     */
    public function getContentService()
    {
        return $this->service( 'ezp\\Content\\Service' );
    }

    /**
     * Get Content Language Service
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \ezp\Content\Language\Service
     */
    public function getContentLanguageService()
    {
        return $this->service( 'ezp\\Content\\Language\\Service' );
    }

    /**
     * Get Content Type Service
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \ezp\Content\Type\Service
     */
    public function getContentTypeService()
    {
        return $this->service( 'ezp\\Content\\Type\\Service' );
    }

    /**
     * Get Content Location Service
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \ezp\Content\Location\Service
     */
    public function getLocationService()
    {
        return $this->service( 'ezp\\Content\\Location\\Service' );
    }

    /**
     * Get Content Trash service
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
     * Get Content Section Service
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \ezp\Content\Section\Service
     */
    public function getSectionService()
    {
        return $this->service( 'ezp\\Content\\Section\\Service' );
    }

    /**
     * Get Io Service
     *
     * Get service object to perform operations on binary files
     *
     * @return \ezp\Io\Service
     */
    public function getIoService()
    {
        return $this->service( 'ezp\\Io\\Service' );
    }

    /**
     * Get User Service
     *
     * Get service object to perform operations on User objects and it's aggregate members.
     * ( UserGroups, UserRole, UserRolePolicy & UserRolePolicyLimitation )
     *
     * @return \ezp\User\Service
     */
    public function getUserService()
    {
        return $this->service( 'ezp\\User\\Service' );
    }

    /**
     * Get internal field type service.
     *
     * Internal api for use by certain Field types
     *
     * @internal
     * @access private
     * @return \ezp\Content\FieldType\Service
     */
    public function getInternalFieldTypeService()
    {
        return $this->service( 'ezp\\Content\\FieldType\\Service' );
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->persistenceHandler->beginTransaction();
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
        $this->persistenceHandler->commit();
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
        $this->persistenceHandler->rollback();
    }
}
