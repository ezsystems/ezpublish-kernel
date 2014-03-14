<?php
/**
 * File containing the eZ\Publish\Core\Repository\Permission\UserService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository\Permission
 */

namespace eZ\Publish\Core\Repository\Permission;


use eZ\Publish\API\Repository\Values\User\UserCreateStruct as APIUserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\User\UserGroup as APIUserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct as APIUserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct as APILocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot as CriterionLogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree as CriterionSubtree;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;

/**
 * This service provides methods for managing users and user groups
 *
 * @example Examples/user.php
 *
 * @package eZ\Publish\Core\Repository\Permission
 */
class UserService implements UserServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $permissionsService;

    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $innerUserService;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\UserService $innerUserService
     * @param PermissionsService $permissionsService
     */
    public function __construct(
        UserServiceInterface $innerUserService,
        PermissionsService $permissionsService
    )
    {
        $this->innerUserService = $innerUserService;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Creates a new user group using the data provided in the ContentCreateStruct parameter
     *
     * In 4.x in the content type parameter in the profile is ignored
     * - the content type is determined via configuration and can be set to null.
     * The returned version is published.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct $userGroupCreateStruct a structure for setting all necessary data to create this user group
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $parentGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the input structure has invalid data
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userGroupCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or set to an empty value
     */
    public function createUserGroup( APIUserGroupCreateStruct $userGroupCreateStruct, APIUserGroup $parentGroup )
    {
        // Needed because permission system needs location info for create permission
        $locationStructs = array(
            new APILocationCreateStruct(
                array( 'parentLocationId' => $parentGroup->contentInfo->mainLocationId )
            )
        );

        if ( $userGroupCreateStruct->ownerId === null )
        {
            $userGroupCreateStruct->ownerId = $this->permissionsService->getCurrentUser()->id;
        }

        if ( !$this->permissionsService->canUser( 'content', 'create', $userGroupCreateStruct, $locationStructs ) )
            throw new UnauthorizedException( 'content', 'create' );

        return $this->innerUserService->createUserGroup( $userGroupCreateStruct, $parentGroup );
    }

    /**
     * Loads a user group for the given id
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function loadUserGroup( $id )
    {
        $userGroup = $this->innerUserService->loadUserGroup( $id );
        if ( !$this->permissionsService->canUser( 'content', 'read', $userGroup ) )
            throw new UnauthorizedException( 'content', 'read' );

        return $userGroup;
    }

    /**
     * Loads the sub groups of a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the user group
     */
    public function loadSubUserGroups( APIUserGroup $userGroup )
    {
        if ( !$this->permissionsService->canUser( 'content', 'read', $userGroup ) )
            throw new UnauthorizedException( 'content', 'read' );

        $subUserGroups = $this->innerUserService->loadSubUserGroups( $userGroup );
        foreach ( $subUserGroups as $subUserGroup )
        {
            if ( !$this->permissionsService->canUser( 'content', 'read', $subUserGroup ) )
                throw new UnauthorizedException( 'content', 'read' );
        }
        return $subUserGroups;
    }

    /**
     * Removes a user group
     *
     * the users which are not assigned to other groups will be deleted.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     */
    public function deleteUserGroup( APIUserGroup $userGroup )
    {
        if ( !$this->permissionsService->canUser( 'content', 'remove', $userGroup ) )
            throw new UnauthorizedException( 'content', 'remove' );

        $this->innerUserService->deleteUserGroup( $userGroup );
    }

    /**
     * Moves the user group to another parent
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     */
    public function moveUserGroup( APIUserGroup $userGroup, APIUserGroup $newParent )
    {
        if ( !$this->permissionsService->canUser( 'content', 'read', $userGroup ) )
            throw new UnauthorizedException( 'content', 'read', array( '\$userGroup' => $userGroup->id ) );

        if ( !$this->permissionsService->canUser( 'content', 'read', $newParent ) )
            throw new UnauthorizedException( 'content', 'read', array( '\$newParent' => $newParent->id ) );

        // Needed because permission system needs location info for create permission
        $locationStructs = array(
            new APILocationCreateStruct(
                array( 'parentLocationId' => $newParent->contentInfo->mainLocationId )
            )
        );

        // check create permission on target location
        if ( !$this->permissionsService->canUser( 'content', 'create', $userGroup, $locationStructs ) )
            throw new UnauthorizedException( 'content', 'create' );

        /** Check read access to whole source subtree
         * @var boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion $contentReadCriterion
         */
        $contentReadCriterion = $this->permissionsService->getPermissionsCriterion();
        if ( $contentReadCriterion === false )
        {
            throw new UnauthorizedException( 'content', 'read' );
        }
        else if ( $contentReadCriterion !== true )
        {
            // Query if there are any content in subtree current user don't have access to
            $query = new Query(
                array(
                    'limit' => 0,
                    'filter' => new CriterionLogicalAnd(
                        new CriterionSubtree( $location->pathString ),
                        new CriterionLogicalNot( $contentReadCriterion )
                    )
                )
            );
            $result = $this->innerSearchService->findContent( $query, array(), false );
            if ( $result->totalCount > 0 )
            {
                throw new UnauthorizedException( 'content', 'read' );
            }
        }

        return $this->innerUserService->moveUserGroup( $userGroup, $newParent );
    }

    /**
     * Updates the group profile with fields and meta data
     *
     * 4.x: If the versionUpdateStruct is set in $userGroupUpdateStruct, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explicitly required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct $userGroupUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userGroupUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     */
    public function updateUserGroup( APIUserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct )
    {
        if ( !$this->permissionsService->canUser( 'content', 'edit', $userGroup ) )
            throw new UnauthorizedException( 'content', 'edit' );

        if ( $userGroupUpdateStruct->contentUpdateStruct->creatorId === null )
        {
            $userGroupUpdateStruct->contentUpdateStruct->creatorId = $this->permissionsService->getCurrentUser()->id;
        }

        return $this->innerUserService->updateUserGroup( $userGroup, $userGroupUpdateStruct );
    }

    /**
     * Create a new user. The created user is published by this method
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserCreateStruct $userCreateStruct the data used for creating the user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup[] $parentGroups the groups which are assigned to the user after creation
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or set to an empty value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a user with provided login already exists
     */
    public function createUser( APIUserCreateStruct $userCreateStruct, array $parentGroups )
    {
        // Needed because permission system needs location info for create permission
        $locationStructs = array();
        foreach ( $parentGroups as $parentGroup )
        {
            $locationStructs[] = new APILocationCreateStruct(
                array( 'parentLocationId' => $parentGroup->contentInfo->mainLocationId )
            );
        }

        if ( $userCreateStruct->ownerId === null )
        {
            $userCreateStruct->ownerId = $this->permissionsService->getCurrentUser()->id;
        }

        if ( !$this->permissionsService->canUser( 'content', 'create', $userCreateStruct, $locationStructs ) )
            throw new UnauthorizedException( 'content', 'create' );

        return $this->innerUserService->createUser( $userCreateStruct, $parentGroups );
    }

    /**
     * Loads a user
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     */
    public function loadUser( $userId )
    {
        return $this->innerUserService->loadUser( $userId );
    }

    /**
     * Loads anonymous user
     *
     * @deprecated since 5.3, use loadUser( $anonymousUserId ) instead
     *
     * @uses loadUser()
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadAnonymousUser()
    {
        return $this->innerUserService->loadAnonymousUser();
    }

    /**
     * Loads a user for the given login and password
     *
     * @param string $login
     * @param string $password the plain password
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given credentials was not found
     */
    public function loadUserByCredentials( $login, $password )
    {
        return $this->innerUserService->loadUserByCredentials( $login, $password );
    }

    /**
     * Loads a user for the given login
     *
     * @param string $login
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given credentials was not found
     */
    public function loadUserByLogin( $login )
    {
        return $this->innerUserService->loadUserByLogin( $login );
    }

    /**
     * Loads a user for the given email
     *
     * Returns an array of Users since eZ Publish has under certain circumstances allowed
     * several users having same email in the past (by means of a configuration option).
     *
     * @param string $email
     *
     * @return \eZ\Publish\API\Repository\Values\User\User[]
     */
    public function loadUsersByEmail( $email )
    {
        return $this->innerUserService->loadUsersByEmail( $email );
    }

    /**
     * This method deletes a user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete the user
     */
    public function deleteUser( APIUser $user )
    {
        if ( !$this->permissionsService->canUser( 'content', 'remove', $user ) )
            throw new UnauthorizedException( 'content', 'remove' );

        $this->innerUserService->deleteUser( $user );
    }

    /**
     * Updates a user
     *
     * 4.x: If the versionUpdateStruct is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explicitly required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserUpdateStruct $userUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUser( APIUser $user, UserUpdateStruct $userUpdateStruct )
    {
        if ( !$this->permissionsService->canUser( 'content', 'edit', $user ) )
            throw new UnauthorizedException( 'content', 'edit' );

        return $this->innerUserService->updateUser( $user, $userUpdateStruct );
    }

    /**
     * Assigns a new user group to the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign the user group to the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is already in the given user group
     */
    public function assignUserToUserGroup( APIUser $user, APIUserGroup $userGroup )
    {
        // Needed because permission system needs location info for create permission
        $locationStructs = array(
            new APILocationCreateStruct(
                array( 'parentLocationId' => $userGroup->contentInfo->mainLocationId )
            )
        );

        if ( !$this->permissionsService->canUser( 'content', 'create', $user, $locationStructs ) )
        {
            throw new UnauthorizedException( 'content', 'create' );
        }

        return $this->innerUserService->assignUserToUserGroup( $user, $userGroup );
    }

    /**
     * Removes a user group from the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove the user group from the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is not in the given user group
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If $userGroup is the last assigned user group
     */
    public function unAssignUserFromUserGroup( APIUser $user, APIUserGroup $userGroup )
    {
        if ( !$this->permissionsService->canUser( 'content', 'manage_locations', $user ) )
            throw new UnauthorizedException( 'content', 'manage_locations' );

        $location = $this->innerLoctionService->loadLocation( $userGroup->contentInfo->mainLocationId );
        if ( !$this->permissionsService->canUser( 'content', 'remove', $user, $location ) )
            throw new UnauthorizedException( 'content', 'remove' );

        /** Check remove access to descendants (yes, user might be container)
         * @var boolean|\eZ\Publish\API\Repository\Values\Content\Query\Criterion $contentReadCriterion
         */
        $contentReadCriterion = $this->permissionsService->getPermissionsCriterion( 'content', 'remove' );
        if ( $contentReadCriterion === false )
        {
            throw new UnauthorizedException( 'content', 'remove' );
        }
        else if ( $contentReadCriterion !== true )
        {
            // Query if there are any content in subtree current user don't have access to
            $query = new Query(
                array(
                    'limit' => 0,
                    'filter' => new CriterionLogicalAnd(
                        array(
                            new CriterionSubtree( $location->pathString ),
                            new CriterionLogicalNot( $contentReadCriterion )
                        )
                    )
                )
            );
            $result = $this->innerSearchService->findContent( $query, array(), false );
            if ( $result->totalCount > 0 )
            {
                throw new UnauthorizedException( 'content', 'remove' );
            }
        }
    }

    /**
     * Loads the user groups the user belongs to
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed read the user or user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    public function loadUserGroupsOfUser( APIUser $user )
    {
        if ( !$this->permissionsService->canUser( 'content', 'edit', $user ) )
            throw new UnauthorizedException( 'content', 'edit' );

        // @todo: Need to find a way to send permission criteria to inner service or avoid use of search
    }

    /**
     * Loads the users of a user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the users or user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\User\User[]
     */
    public function loadUsersOfUserGroup( APIUserGroup $userGroup, $offset = 0, $limit = -1 )
    {
        if ( !$this->permissionsService->canUser( 'content', 'edit', $userGroup ) )
            throw new UnauthorizedException( 'content', 'edit' );

        // @todo: Need to find a way to send permission criteria to inner service or avoid use of search
    }

    /**
     * Instantiate a user create class
     *
     * @param string $login the login of the new user
     * @param string $email the email of the new user
     * @param string $password the plain password of the new user
     * @param string $mainLanguageCode the main language for the underlying content object
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserCreateStruct
     */
    public function newUserCreateStruct( $login, $email, $password, $mainLanguageCode, $contentType = null )
    {
        return $this->innerUserService->newUserCreateStruct(
            $login,
            $email,
            $password,
            $mainLanguageCode,
            $contentType
        );
    }

    /**
     * Instantiate a user group create class
     *
     * @param string $mainLanguageCode The main language for the underlying content object
     * @param null|\eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct
     */
    public function newUserGroupCreateStruct( $mainLanguageCode, $contentType = null )
    {
        return $this->innerUserService->newUserGroupCreateStruct( $mainLanguageCode, $contentType );
    }

    /**
     * Instantiate a new user update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct()
    {
        return $this->innerUserService->newUserUpdateStruct();
    }

    /**
     * Instantiate a new user group update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct()
    {
        return $this->innerUserService->newUserGroupUpdateStruct();
    }
}
