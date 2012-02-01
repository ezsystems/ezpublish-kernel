<?php
/**
 * @package eZ\Publish\API\Interfaces
 */
namespace eZ\Publish\API\Interfaces;

use eZ\Publish\API\Values\User\UserCreateStruct;
use eZ\Publish\API\Values\User\UserUpdateStruct;
use eZ\Publish\API\Values\User\User;
use eZ\Publish\API\Values\User\UserGroup;
use eZ\Publish\API\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Values\User\UserGroupUpdateStruct;

/**
 * This service provides methods for managing users and user groups
 *
 * @example Examples/user.php
 *
 * @package eZ\Publish\API\Interfaces
 */
interface UserService
{
    /**
     * Creates a new user group using the data provided in the ContentCreateStruct parameter
     *
     * In 4.x in the content type parameter in the profile is ignored
     * - the content type is determined via configuration and can be set to null.
     * The returned version is published.
     *
     * @param \eZ\Publish\API\Values\User\UserGroupCreateStruct $userGroupCreateStruct a structure for setting all necessary data to create this user group
     * @param \eZ\Publish\API\Values\User\UserGroup $parentGroup
     *
     * @return \eZ\Publish\API\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Exceptions\IllegalArgumentException if the input structure has invalid data
     * @throws \eZ\Publish\API\Exceptions\ContentFieldValidationException if a field in the $userGroupCreateStruct is not valid
     * @throws \eZ\Publish\API\Exceptions\ContentValidationException if a required field is missing
     */
    public function createUserGroup( UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup );

    /**
     * Loads a user group for the given id
     *
     * @param int $id
     *
     * @return \eZ\Publish\API\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function loadUserGroup( $id );

    /**
     * Loads the sub groups of a user group
     *
     * @param \eZ\Publish\API\Values\User\UserGroup $userGroup
     *
     * @return array an array of {@link \eZ\Publish\API\Values\User\UserGroup}
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the user group
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function loadSubUserGroups( UserGroup $userGroup );

    /**
     * Removes a user group
     *
     * the users which are not assigned to other groups will be deleted.
     *
     * @param \eZ\Publish\API\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function deleteUserGroup( UserGroup $userGroup );

    /**
     * Moves the user group to another parent
     *
     * @param \eZ\Publish\API\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function moveUserGroup( UserGroup $userGroup, UserGroup $newParent );

    /**
     * Updates the group profile with fields and meta data
     *
     * 4.x: If the versionUpdateStruct is set in $userGroupUpdateStruct, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explititely required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Values\User\UserGroupUpdateStruct $userGroupUpdateStruct
     *
     * @return \eZ\Publish\API\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Exceptions\ContentFieldValidationException if a field in the $userGroupUpdateStruct is not valid
     * @throws \eZ\Publish\API\Exceptions\ContentValidationException if a required field is set empty
     */
    public function updateUserGroup( UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct );

    /**
     * Create a new user. The created user is published by this method
     *
     * @param \eZ\Publish\API\Values\User\UserCreateStruct $userCreateStruct the data used for creating the user
     * @param array $parentGroups the groups of type {@link \eZ\Publish\API\Values\User\UserGroup} which are assigned to the user after creation
     *
     * @return \eZ\Publish\API\Values\User\User
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if a user group was not found
     * @throws \eZ\Publish\API\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Exceptions\ContentValidationException if a required field is missing
     */
    public function createUser( UserCreateStruct $userCreateStruct, array $parentGroups );

    /**
     * Loads a user
     *
     * @param integer $userId
     *
     * @return \eZ\Publish\API\Values\User\User
     *
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if a user with the given id was not found
     */
    public function loadUser( $userId );

    /**
     * Loads a user for the given login and password
     *
     * @param string $login
     * @param string $password the plain password
     *
     * @return \eZ\Publish\API\Values\User\User
     *
     * @throws \eZ\Publish\API\Exceptions\NotFoundException if a user with the given credentials was not found
     */
    public function loadUserByCredentials( $login, $password );

    /**
     * This method deletes a user
     *
     * @param \eZ\Publish\API\Values\User\User $user
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete the user
     */
    public function deleteUser( User $user );

    /**
     * Updates a user
     *
     * 4.x: If the versionUpdateStruct is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explititely required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Values\User\User $user
     * @param \eZ\Publish\API\Values\User\UserUpdateStruct
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user
     * @throws \eZ\Publish\API\Exceptions\ContentFieldValidationException if a field in the $userUpdateStruct is not valid
     * @throws \eZ\Publish\API\Exceptions\ContentValidationException if a required field is set empty
     */
    public function updateUser( User $user, UserUpdateStruct $userUpdateStruct );

    /**
     * Assigns a new user group to the user
     *
     * If the user is already in the given user group this method does nothing.
     *
     * @param \eZ\Publish\API\Values\User\User $user
     * @param \eZ\Publish\API\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign the user group to the user
     */
    public function assignUserToUserGroup( User $user, UserGroup $userGroup );

    /**
     * Removes a user group from the user
     *
     * @param \eZ\Publish\API\Values\User\User $user
     * @param \eZ\Publish\API\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove the user group from the user
     * @throws \eZ\Publish\API\Exceptions\IllegalArgumentException if the user is not in the given user group
     */
    public function unAssignUssrFromUserGroup( User $user, UserGroup $userGroup );

    /**
     * Instantiate a user create class
     *
     * @param string $login the login of the new user
     * @param string $email the email of the new user
     * @param string $password the plain password of the new user
     * @param string $mainLanguageCode the main language for the underlying content object
     * @param ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Values\User\UserCreateStruct
     */
    public function newUserCreateStruct( $login, $email, $password, $mainLanguageCode, $contentType = null );

    /**
     * Instantiate a user group create class
     *
     * @param string $mainLanguageCode The main language for the underlying content object
     * @param null|\eZ\Publish\API\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Values\User\UserGroupCreateStruct
     */
    public function newUserGroupCreateStruct( $mainLanguageCode, $contentType = null );

    /**
     * Instantiate a new user update struct
     *
     * @return \eZ\Publish\API\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct();

    /**
     * Instantiate a new user group update struct
     *
     * @return \eZ\Publish\API\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct();
}
