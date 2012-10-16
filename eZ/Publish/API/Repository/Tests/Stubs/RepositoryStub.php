<?php
/**
 * File containing the RepositoryStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\Repository;
use \eZ\Publish\API\Repository\Values\ValueObject;
use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\Content\ContentInfo;
use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\VersionInfo;
use \eZ\Publish\API\Repository\Values\User\User;
use \eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Repository}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Repository
 */
class RepositoryStub implements Repository
{
    /**
     * @var string
     */
    private $fixtureDir;

    /**
     * @var integer
     */
    private $version;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $currentUser;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\SectionServiceStub
     */
    private $sectionService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\LanguageServiceStub
     */
    private $languageService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\UserServiceStub
     */
    private $userService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RoleServiceStub
     */
    private $roleService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\ContentServiceStub
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\ContentTypeServiceStub
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\TrashServiceStub
     */
    private $trashService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\LocationServiceStub
     */
    private $locationService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\IOServiceStub
     */
    private $ioService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\URLAliasServiceStub
     */
    private $urlAliasService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\URLWildcardServiceStub
     */
    private $urlWildcardService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\ObjectStateService
     */
    private $objectStateService;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\FieldTypeServiceStub
     */
    private $fieldTypeService;

    /**
     * @var integer
     */
    private $transactionDepth = 0;

    /**
     * @var integer
     */
    private $permissionChecks = 0;

    /**
     * Instantiates the stubbed repository.
     *
     * @param string $fixtureDir
     * @param integer $version
     */
    public function __construct( $fixtureDir, $version )
    {
        $this->fixtureDir = $fixtureDir;
        $this->version = $version;
    }

    /**
     * Get current user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Sets the current user to the user with the given user id
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return void
     */
    public function setCurrentUser( User $user )
    {
        $this->currentUser = $user;
    }

    /**
     *
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return boolean|\eZ\Publish\API\Repository\Values\User\Limitation[] if limitations are on this function an array of limitations is returned
     */
    public function hasAccess( $module, $function, User $user = null )
    {
        if ( $this->permissionChecks > 0 )
        {
            return true;
        }

        $user = $user ?: $this->getCurrentUser();
        $roleService = $this->getRoleService();

        ++$this->permissionChecks;

        foreach ( $roleService->getRoleAssignmentsForUser( $user, true ) as $roleAssignment )
        {
            $roleLimitation = $roleAssignment->getRoleLimitation();
            $permissionSet = array( 'limitation' => null, 'policies' => array() );
            foreach ( $roleService->__getRolePolicies( $roleAssignment->getRole() ) as $policy )
            {
                if ( $policy->module === '*' && $roleLimitation === null )
                {
                    --$this->permissionChecks;
                    return true;
                }

                if ( $policy->module !== $module )
                    continue;

                if ( $policy->function === '*' && $roleLimitation === null )
                {
                    --$this->permissionChecks;
                    return true;
                }

                if ( $policy->function !== $function )
                    continue;

                $permissionSet['policies'][] = $policy;
            }

            if ( !empty( $permissionSet['policies'] ) )
            {
                if ( $roleLimitation !== null )
                {
                    $permissionSet['limitation'] = $roleLimitation;
                }

                $permissionSets[] = $permissionSet;
            }
        }

        --$this->permissionChecks;

        if ( !empty( $permissionSets ) )
            return $permissionSets;

        return false;// No policies matching $module and $function
    }

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *
     * @param string $module The module, aka controller identifier to check permissions on
     * @param string $function The function, aka the controller action to check permissions on
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to check if the user has access to
     * @param \eZ\Publish\API\Repository\Values\ValueObject $target The location, parent or "assignment" value object
     *
     * @return boolean
     */
    public function canUser( $module, $function, ValueObject $object, ValueObject $target = null )
    {
        if ( $this->permissionChecks > 0 )
        {
            return true;
        }

        $permissionSets = $this->hasAccess( $module, $function );
        if ( $permissionSets === false || $permissionSets === true )
        {
            return $permissionSets;
        }

        ++$this->permissionChecks;

        $locations = null;
        $contentInfoValue = null;
        if ( $object instanceof ContentInfo )
        {
            $contentInfoValue = $object;
        }
        else if ( $object instanceof Content )
        {
            $contentInfoValue = $object->contentInfo;
        }
        else if ( $object instanceof VersionInfo )
        {
            $contentInfoValue = $object->contentInfo;
        }
        else if ( $object instanceof Location )
        {
            $locations = array( $object );
        }

        if ( null !== $contentInfoValue && true === $contentInfoValue->published )
        {
            $locationService = $this->getLocationService();
            $locations = $locationService->loadLocations( $contentInfoValue );
        }

        if ( null === $locations )
        {
            --$this->permissionChecks;
            return true;
         }

        foreach ( $permissionSets as $permissionSet )
        {
            /**
             * @var \eZ\Publish\API\Repository\Values\User\Limitation[] $permissionSet
             */
            if ( $permissionSet['limitation'] instanceof Limitation )
            {
                if ( $permissionSet['limitation']->getIdentifier() == Limitation::SUBTREE )
                {
                    foreach ( $locations as $location )
                    {
                        foreach ( $permissionSet['limitation']->limitationValues as $limitationPathString )
                        {
                            if ( strpos( $location->pathString, $limitationPathString ) === 0 )
                            {
                                --$this->permissionChecks;
                                return true;
                            }
                        }
                    }
                }
                else if ( $permissionSet['limitation']->getIdentifier() == Limitation::SECTION )
                {
                    if ( in_array( $contentInfoValue->sectionId, $permissionSet['limitation']->limitationValues ) )
                    {
                        --$this->permissionChecks;
                        return true;
                    }
                }
            }
        }

        --$this->permissionChecks;
        return false; // None of the limitation sets wanted to let you in, sorry!
    }

    /**
     * Get Content Service
     *
     * Get service object to perform operations on Content objects and it's aggregate members.
     *
     * @return \eZ\Publish\API\Repository\ContentService
     */
    public function getContentService()
    {
        if ( null === $this->contentService )
        {
            $this->contentService = new ContentServiceStub( $this );
        }
        return $this->contentService;
    }

    /**
     * Get Content Language Service
     *
     * Get service object to perform operations on Content language objects
     *
     * @return \eZ\Publish\API\Repository\LanguageService
     */
    public function getContentLanguageService()
    {
        if ( null === $this->languageService )
        {
            $this->languageService = new LanguageServiceStub(
                $this,
                $this->getContentService(),
                'eng-US'
            );
        }
        return $this->languageService;
    }

    /**
     * Get Content Type Service
     *
     * Get service object to perform operations on Content Type objects and it's aggregate members.
     * ( Group, Field & FieldCategory )
     *
     * @return \eZ\Publish\API\Repository\ContentTypeService
     */
    public function getContentTypeService()
    {
        if ( null === $this->contentTypeService )
        {
            $this->contentTypeService = new ContentTypeServiceStub(
                $this,
                $this->getContentService()
            );
        }
        return $this->contentTypeService;
    }

    /**
     * Get Content Location Service
     *
     * Get service object to perform operations on Location objects and subtrees
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    public function getLocationService()
    {
        if ( null === $this->locationService )
        {
            $this->locationService = new LocationServiceStub( $this );
        }
        return $this->locationService;
    }

    /**
     * Get Content Trash service
     *
     * Trash service allows to perform operations related to location trash
     * (trash/untrash, load/list from trash...)
     *
     * @return \eZ\Publish\API\Repository\TrashService
     */
    public function getTrashService()
    {
        if ( null === $this->trashService )
        {
            $this->trashService = new TrashServiceStub(
                $this,
                $this->getLocationService()
            );
        }
        return $this->trashService;
    }

    /**
     * Get Content Section Service
     *
     * Get Section service that lets you manipulate section objects
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    public function getSectionService()
    {
        if ( null === $this->sectionService )
        {
            $this->sectionService = new SectionServiceStub( $this );
        }
        return $this->sectionService;
    }

    /**
     * Get Search Service
     *
     * Get search service that lets you find content objects
     *
     * @return \eZ\Publish\API\Repository\SearchService
     */
    public function getSearchService()
    {
        throw new \RuntimeException( '@TODO: Implememt.' );
    }

    /**
     * Get User Service
     *
     * Get service object to perform operations on Users and UserGroup
     *
     * @return \eZ\Publish\API\Repository\UserService
     */
    public function getUserService()
    {
        if ( null === $this->userService )
        {
            $this->userService = new UserServiceStub( $this );
        }
        return $this->userService;
    }

    /**
     * Get IO Service
     *
     * Get service object to perform operations on binary files
     *
     * @return \eZ\Publish\API\Repository\IOService
     */
    public function getIOService()
    {
        if ( null === $this->ioService )
        {
            $this->ioService = new IOServiceStub( $this );
        }
        return $this->ioService;
    }

    /**
     * Get RoleService
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService()
    {
        if ( null === $this->roleService )
        {
            $this->roleService = new RoleServiceStub( $this, $this->getUserService() );
        }
        return $this->roleService;
    }

    /**
     * Get URLAliasService
     *
     * @return \eZ\Publish\API\Repository\URLAliasService
     */
    public function getURLAliasService()
    {
        if ( null === $this->urlAliasService )
        {
            $this->urlAliasService = new URLAliasServiceStub( $this );
        }
        return $this->urlAliasService;
    }

    /**
     * Get URLWildcardService
     *
     * @return \eZ\Publish\API\Repository\URLWildcardService
     */
    public function getURLWildcardService()
    {
        if ( null === $this->urlWildcardService )
        {
            $this->urlWildcardService = new URLWildcardServiceStub( $this );
        }
        return $this->urlWildcardService;
    }

    /**
     * Get ObjectStateService
     *
     * @return \eZ\Publish\API\Repository\ObjectStateService
     */
    public function getObjectStateService()
    {
        if ( null === $this->objectStateService )
        {
            $this->objectStateService = new ObjectStateServiceStub( $this );
        }
        return $this->objectStateService;
    }

    /**
     * Get FieldTypeService
     *
     * @return \eZ\Publish\API\Repository\FieldTypeService
     */
    public function getFieldTypeService()
    {
        if ( null === $this->fieldTypeService )
        {
            $this->fieldTypeService = new FieldTypeServiceStub( $this );
        }
        return $this->fieldTypeService;
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        ++$this->transactionDepth;
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
        if ( 0 === $this->transactionDepth )
        {
            throw new \RuntimeException( 'What error code should be used?' );
        }
        --$this->transactionDepth;
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
        if ( 0 === $this->transactionDepth )
        {
            throw new \RuntimeException( 'What error code should be used?' );
        }

        if ( $this->contentService )
        {
            $this->contentService->__rollback();
        }
        if ( $this->contentTypeService )
        {
            $this->contentTypeService->__rollback();
        }
        if ( $this->ioService )
        {
            $this->ioService->__rollback();
        }
        if ( $this->languageService )
        {
            $this->languageService->__rollback();
        }
        if ( $this->locationService )
        {
            $this->locationService->__rollback();
        }
        if ( $this->roleService )
        {
            $this->roleService->__rollback();
        }
        if ( $this->sectionService )
        {
            $this->sectionService->__rollback();
        }
        if ( $this->trashService )
        {
            $this->trashService->__rollback();
        }
        if ( $this->userService )
        {
            $this->userService->__rollback();
        }

        --$this->transactionDepth;
    }

    /**
     * Internally helper method that returns pre defined test data.
     *
     * @param string $fixtureName
     * @param mixed[] $scopeValues
     *
     * @return array
     */
    public function loadFixture( $fixtureName, array $scopeValues = array() )
    {
        ++$this->permissionChecks;
        $fixture = include $this->fixtureDir . '/' . $fixtureName . 'Fixture.php';
        --$this->permissionChecks;

        return $fixture;
    }

    /**
     * Internal helper method used to disable permission checks.
     *
     * @return void
     */
    public function disableUserPermissions()
    {
        ++$this->permissionChecks;
    }

    /**
     * Internal helper method used to enable permission checks.
     *
     * @return void
     */
    public function enableUserPermissions()
    {
        --$this->permissionChecks;
    }

    /**
     * Only for internal use.
     *
     * Creates a \DateTime object for $timestamp in the current time zone
     *
     * @param int $timestamp
     * @return \DateTime
     */
    public function createDateTime( $timestamp = null )
    {
        $dateTime = new \DateTime();
        if ( $timestamp !== null )
        {
            $dateTime->setTimestamp( $timestamp );
        }
        return $dateTime;
    }
}
