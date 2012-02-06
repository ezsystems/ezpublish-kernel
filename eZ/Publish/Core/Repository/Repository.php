<?php
/**
 * Repository class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;
use eZ\Publish\Core\Base\Exceptions\BadConfiguration,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\Logic,
    eZ\Publish\SPI\IO\Handler as IoHandler,
    eZ\Publish\SPI\Persistence\Handler as PersistenceHandler,
    eZ\Publish\API\Repository\Repository  as RepositoryInterface,
    eZ\Publish\Core\Repository\ContentService,
    eZ\Publish\Core\Repository\LanguageService,
    eZ\Publish\Core\Repository\LocationService,
    eZ\Publish\Core\Repository\SectionService,
    eZ\Publish\Core\Repository\ContentTypeService,
    eZ\Publish\Core\Repository\RoleService,
    eZ\Publish\Core\Repository\UserService,
    eZ\Publish\API\Repository\Values\ValueObject,
    eZ\Publish\API\Repository\Values\User\User,
    RuntimeException;

/**
 * Repository class
 *
 */
class Repository implements RepositoryInterface
{
    /**
     * Repository Handler object
     *
     * @var PersistenceHandler
     */
    protected $persistenceHandler;

    /**
     * Io Handler object
     *
     * @var IoHandler
     */
    protected $ioHandler;

    /**
     * Currently logged in user object for permission purposes
     *
     * @var User
     */
    protected $user;

    /**
     * Instance of section service
     *
     * @var SectionService
     */
    protected $sectionService;

    /**
     * Instance of role service
     *
     * @var RoleService
     */
    protected $roleService;

    /**
     * Constructor
     *
     * Construct repository object with provided storage engine
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param \eZ\Publish\SPI\IO\Handler $ioHandler
     * @param User|null $user
     */
    public function __construct( PersistenceHandler $persistenceHandler, IoHandler $ioHandler, User $user = null )
    {
        $this->persistenceHandler = $persistenceHandler;
        $this->ioHandler = $ioHandler;

        if ( $user !== null )
            $this->setCurrentUser( $user );
       else
       {
           // @todo No requirement for user for the time being
           //throw new Logic( "@todo Need to get anon user", "repository needs to have a user object" );
       }
    }

    /**
     * Get current user
     *
     * @return User
     */
    function getCurrentUser()
    {
        return $this->user;
    }

    /**
     * Set current user
     *
     * @param User $user
     * @return User Old user
     *
     * @throws InvalidArgumentValue If provided user does not have a valid id value
     */
    function setCurrentUser( User $user )
    {
        if ( !$user->id )
            throw new InvalidArgumentValue( '$user->id', $user->id );

        $oldUser = $this->user;
        $this->user = $user;
        return $oldUser;
    }

    /**
     *
     *
     * @param string $module
     * @param string $function
     * @param User $user
     * @return boolean|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess( $module, $function, User $user = null )
    {
        //@todo implement, see impl in ezp-next
    }

    /**
     * Check if current user has access to a certain function on a model
     *
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects
     *
     * @param string $module
     * @param string $function
     * @param ValueObject $value
     * @param ValueObject $target
     * @return boolean
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue On invalid $function value
     * @throws \eZ\Publish\Core\Base\Exceptions\Logic On limitation used in policies but not in $model::defintion()
     */
    public function canUser( $module, $function, ValueObject $value, ValueObject $target )
    {
        $className = $value;
        $limitationArray = $this->hasAccess( $module, $function );
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

        /**
         * @todo Somewhere to get limitation logic from (functions), then $value should impl a interface
         * that tells us where to get it from for instance.
         * @var array $functions
         */
        $functions = $value::getLimitationFunctions();
        foreach ( $limitationArray as $limitationSet )
        {
            $limitationSetSaysYes = true;
            foreach ( $limitationSet as $limitationKey => $limitationValues )
            {
                if ( !isset( $functions[$function][$limitationKey]['compare'] ) )
                {
                    throw new Logic(
                        "\$definition[functions][{$function}][{$limitationKey}][compare]",
                        "could not find limitation compare function on {$className}::definition()"
                    );
                }

                $limitationCompareFn = $functions[$function][$limitationKey]['compare'];
                if ( !is_callable( $limitationCompareFn ) )
                {
                    throw new Logic(
                        "\$definition[functions][{$function}][{$limitationKey}][compare]",
                        "compare function from {$className}::definition() is not callable"
                    );
                }

                if ( !$limitationCompareFn( $value, $limitationValues, $this, $target ) )
                {
                    $limitationSetSaysYes = false;
                    // Break to next limitationSet
                    break;
                    // If needed, there could be a if condition here building up an array of all limitations
                    // that are denying user access
                }
            }
            if ( $limitationSetSaysYes )
                return true;
        }
        return false;
    }

    /**
     * Get Content Service
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    public function getContentService(){}

    /**
     * Get Content Language Service
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Repository\LanguageService
     */
    public function getContentLanguageService(){}

    /**
     * Get Content Type Service
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    public function getContentTypeService(){}

    /**
     * Get Content Location Service
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    public function getLocationService(){}

    /**
     * Get Content Trash service
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Repository\TrashService
     */
    public function getTrashService(){}

    /**
     * Get Content Section Service
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    public function getSectionService()
    {
        if ( $this->sectionService !== null )
            return $this->sectionService;

        $this->sectionService = new SectionService( $this, $this->persistenceHandler );
        return $this->sectionService;
    }

    /**
     * Get User Service
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    public function getUserService(){}

    /**
     * Get RoleService
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService()
    {
        if ( $this->roleService !== null )
            return $this->roleService;

        $this->roleService = new RoleService( $this, $this->persistenceHandler );
        return $this->roleService;
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
