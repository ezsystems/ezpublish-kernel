<?php
/**
 * UserService class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\SignalSlot;

use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\CreateUserGroupSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\DeleteUserGroupSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\MoveUserGroupSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\UpdateUserGroupSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\CreateUserSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\DeleteUserSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\UpdateUserSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal;
use eZ\Publish\Core\Repository\SignalSlot\Signal\UserService\UnAssignUserFromUserGroupSignal;

/**
 * UserService class
 * @package eZ\Publish\Core\Repository\SignalSlot
 */
class UserService implements UserServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\UserService $service
     * @param \eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( UserServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
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
    public function createUserGroup( UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup )
    {
        $returnValue = $this->service->createUserGroup( $userGroupCreateStruct, $parentGroup );
        $this->signalDispatcher->emit(
            new CreateUserGroupSignal(
                array(
                    'userGroupId' => $returnValue->id,
                )
            )
        );
        return $returnValue;
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
        return $this->service->loadUserGroup( $id );
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
    public function loadSubUserGroups( UserGroup $userGroup )
    {
        return $this->service->loadSubUserGroups( $userGroup );
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
    public function deleteUserGroup( UserGroup $userGroup )
    {
        $returnValue = $this->service->deleteUserGroup( $userGroup );
        $this->signalDispatcher->emit(
            new DeleteUserGroupSignal(
                array(
                    'userGroupId' => $userGroup->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Moves the user group to another parent
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     */
    public function moveUserGroup( UserGroup $userGroup, UserGroup $newParent )
    {
        $returnValue = $this->service->moveUserGroup( $userGroup, $newParent );
        $this->signalDispatcher->emit(
            new MoveUserGroupSignal(
                array(
                    'userGroupId' => $userGroup->id,
                    'newParentId' => $newParent->id,
                )
            )
        );
        return $returnValue;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userGroupUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     */
    public function updateUserGroup( UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct )
    {
        $returnValue = $this->service->updateUserGroup( $userGroup, $userGroupUpdateStruct );
        $this->signalDispatcher->emit(
            new UpdateUserGroupSignal(
                array(
                    'userGroupId' => $userGroup->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Create a new user. The created user is published by this method
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserCreateStruct $userCreateStruct the data used for creating the user
     * @param array $parentGroups the groups of type {@link \eZ\Publish\API\Repository\Values\User\UserGroup} which are assigned to the user after creation
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or set  to an empty value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     *                                                                        if a user with provided login already exists
     */
    public function createUser( UserCreateStruct $userCreateStruct, array $parentGroups )
    {
        $returnValue = $this->service->createUser( $userCreateStruct, $parentGroups );
        $this->signalDispatcher->emit(
            new CreateUserSignal(
                array(
                    'userId' => $returnValue->id,
                )
            )
        );
        return $returnValue;
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
        return $this->service->loadUser( $userId );
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
        return $this->service->loadAnonymousUser();
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
        return $this->service->loadUserByCredentials( $login, $password );
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
        return $this->service->loadUserByLogin( $login );
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
        return $this->service->loadUsersByEmail( $email );
    }

    /**
     * This method deletes a user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete the user
     */
    public function deleteUser( User $user )
    {
        $returnValue = $this->service->deleteUser( $user );
        $this->signalDispatcher->emit(
            new DeleteUserSignal(
                array(
                    'userId' => $user->id,
                )
            )
        );
        return $returnValue;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUser( User $user, UserUpdateStruct $userUpdateStruct )
    {
        $returnValue = $this->service->updateUser( $user, $userUpdateStruct );
        $this->signalDispatcher->emit(
            new UpdateUserSignal(
                array(
                    'userId' => $user->id,
                )
            )
        );
        return $returnValue;
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
    public function assignUserToUserGroup( User $user, UserGroup $userGroup )
    {
        $returnValue = $this->service->assignUserToUserGroup( $user, $userGroup );
        $this->signalDispatcher->emit(
            new AssignUserToUserGroupSignal(
                array(
                    'userId' => $user->id,
                    'userGroupId' => $userGroup->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Removes a user group from the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove the user group from the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is not in the given user group
     */
    public function unAssignUserFromUserGroup( User $user, UserGroup $userGroup )
    {
        $returnValue = $this->service->unAssignUserFromUserGroup( $user, $userGroup );
        $this->signalDispatcher->emit(
            new UnAssignUserFromUserGroupSignal(
                array(
                    'userId' => $user->id,
                    'userGroupId' => $userGroup->id,
                )
            )
        );
        return $returnValue;
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
    public function loadUserGroupsOfUser( User $user )
    {
        return $this->service->loadUserGroupsOfUser( $user );
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
    public function loadUsersOfUserGroup( UserGroup $userGroup, $offset = 0, $limit = -1 )
    {
        return $this->service->loadUsersOfUserGroup( $userGroup, $offset, $limit );
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
        return $this->service->newUserCreateStruct( $login, $email, $password, $mainLanguageCode, $contentType );
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
        return $this->service->newUserGroupCreateStruct( $mainLanguageCode, $contentType );
    }

    /**
     * Instantiate a new user update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct()
    {
        return $this->service->newUserUpdateStruct();
    }

    /**
     * Instantiate a new user group update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct()
    {
        return $this->service->newUserGroupUpdateStruct();
    }
}
