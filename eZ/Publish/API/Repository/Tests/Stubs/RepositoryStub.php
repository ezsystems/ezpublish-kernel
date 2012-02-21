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
use \eZ\Publish\API\Repository\Values\User\User;
use \eZ\Publish\API\Repository\Values\ValueObject;

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
     * Instantiates the stubbed repository.
     *
     * @param string $fixtureDir
     */
    public function __construct( $fixtureDir )
    {
        $this->fixtureDir = $fixtureDir;
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
     * @return boolean|array if limitations are on this function an array of limitations is returned
     */
    public function hasAccess( $module, $function, User $user = null )
    {
        $limitations = array();

        $user = $user ?: $this->getCurrentUser();

        $roleService = $this->getRoleService();
        foreach ( $roleService->loadPoliciesByUserId( $user->id ) as $policy )
        {
            if ( $policy->module === '*' )
            {
                return true;
            }
            if ( $policy->module !== $module )
            {
                continue;
            }
            if ( $policy->function === '*' )
            {
                return true;
            }
            if ( $policy->function !== $function )
            {
                continue;
            }

            // TODO: $policy->getLimitations() === '*'

            foreach ( $policy->getLimitations() as $limitation )
            {
                $limitations[] = $limitation;
            }
        }

        if ( 0 === count( $limitations ) )
        {
            return $limitations;
        }
        return false;
    }

    /**
     * Indicates if the current user is allowed to perform an action given by the function on the given
     * objects
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\ValueObject $value
     * @param \eZ\Publish\API\Repository\Values\ValueObject $target
     */
    public function canUser( $module, $function, ValueObject $value, ValueObject $target )
    {
        // TODO: Implement canUser() method.
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
            $this->languageService = new LanguageServiceStub( $this );
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
            $this->contentTypeService = new ContentTypeServiceStub();
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
        // TODO: Implement getLocationService() method.
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
        // TODO: Implement getTrashService() method.
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
     * Get RoleService
     *
     * @return \eZ\Publish\API\Repository\RoleService
     */
    public function getRoleService()
    {
        if ( null === $this->roleService )
        {
            $this->roleService = new RoleServiceStub( $this );
        }
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
        // TODO: Implement beginTransaction() method.
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
        // TODO: Implement commit() method.
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
        // TODO: Implement rollback() method.
    }

    /**
     * Internally helper method that returns pre defined test data.
     *
     * @param string $fixtureName
     * @return array
     */
    public function loadFixture( $fixtureName )
    {
        return include $this->fixtureDir . '/' . $fixtureName . 'Fixture.php';
    }
}
