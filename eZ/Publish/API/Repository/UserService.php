<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\User\PasswordInfo;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;

/**
 * This service provides methods for managing users and user groups.
 */
interface UserService
{
    /**
     * Creates a new user group using the data provided in the ContentCreateStruct parameter.
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
    public function createUserGroup(UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup): UserGroup;

    /**
     * Loads a user group for the given id.
     *
     * @param int $id
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function loadUserGroup(int $id, array $prioritizedLanguages = []): UserGroup;

    /**
     * Loads the sub groups of a user group.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param int $offset the start offset for paging
     * @param int $limit the number of user groups returned
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the user group
     */
    public function loadSubUserGroups(UserGroup $userGroup, int $offset = 0, int $limit = 25, array $prioritizedLanguages = []): iterable;

    /**
     * Removes a user group.
     *
     * the users which are not assigned to other groups will be deleted.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     *
     * @return int[] Affected Location Id's (List of Locations of the Content that was deleted)
     */
    public function deleteUserGroup(UserGroup $userGroup): iterable;

    /**
     * Moves the user group to another parent.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     */
    public function moveUserGroup(UserGroup $userGroup, UserGroup $newParent): void;

    /**
     * Updates the group profile with fields and meta data.
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
    public function updateUserGroup(UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct): UserGroup;

    /**
     * Create a new user. The created user is published by this method.
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
    public function createUser(UserCreateStruct $userCreateStruct, array $parentGroups): User;

    /**
     * Loads a user.
     *
     * @param mixed $userId
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     */
    public function loadUser(int $userId, array $prioritizedLanguages = []): User;

    /**
     * Loads a user for the given login.
     *
     * Since 6.1 login is case-insensitive across all storage engines and database backends, like was the case
     * with mysql before in eZ Publish 3.x/4.x/5.x.
     *
     * @deprecated since eZ Platform 2.5, will be dropped in the next major version as authentication
     *             may depend on various user providers. Use UserService::checkUserCredentials() instead.
     *
     * @param string $login
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given credentials was not found
     */
    public function loadUserByLogin(string $login, array $prioritizedLanguages = []): User;

    /**
     * Checks if credentials are valid for provided User.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param string $credentials
     *
     * @return bool
     */
    public function checkUserCredentials(User $user, string $credentials): bool;

    /**
     * Loads a user for the given email.
     *
     * @param string $email
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function loadUserByEmail(string $email, array $prioritizedLanguages = []): User;

    /**
     * Loads a users for the given email.
     *
     * Note: This method loads user by $email where $email might be case-insensitive on certain storage engines!
     *
     * Returns an array of Users since eZ Publish has under certain circumstances allowed
     * several users having same email in the past (by means of a configuration option).
     *
     * @param string $email
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function loadUsersByEmail(string $email, array $prioritizedLanguages = []): iterable;

    /**
     * Loads a user with user hash key.
     *
     * @param string $hash
     * @param string[] $prioritizedLanguages
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadUserByToken(string $hash, array $prioritizedLanguages = []): User;

    /**
     * This method deletes a user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete the user
     *
     * @return int[] Affected Location Id's (List of Locations of the Content that was deleted)
     */
    public function deleteUser(User $user): iterable;

    /**
     * Updates a user.
     *
     * 4.x: If the versionUpdateStruct is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explicitly required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserUpdateStruct $userUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     *@throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user
     */
    public function updateUser(User $user, UserUpdateStruct $userUpdateStruct): User;

    /**
     * Update the user token information specified by the user token struct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct $userTokenUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUserToken(User $user, UserTokenUpdateStruct $userTokenUpdateStruct): User;

    /**
     * Expires user token with user hash.
     *
     * @param string $hash
     */
    public function expireUserToken(string $hash): void;

    /**
     * Assigns a new user group to the user.
     *
     * If the user is already in the given user group this method does nothing.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign the user group to the user
     */
    public function assignUserToUserGroup(User $user, UserGroup $userGroup): void;

    /**
     * Removes a user group from the user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove the user group from the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is not in the given user group
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If $userGroup is the last assigned user group
     */
    public function unAssignUserFromUserGroup(User $user, UserGroup $userGroup): void;

    /**
     * Loads the user groups the user belongs to.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed read the user or user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param int $offset the start offset for paging
     * @param int $limit the number of user groups returned
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    public function loadUserGroupsOfUser(User $user, int $offset = 0, int $limit = 25, array $prioritizedLanguages = []): iterable;

    /**
     * Loads the users of a user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the users or user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param int $offset the start offset for paging
     * @param int $limit the number of users returned
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User[]
     */
    public function loadUsersOfUserGroup(UserGroup $userGroup, int $offset = 0, int $limit = 25, array $prioritizedLanguages = []): iterable;

    /**
     * Checks if Content is a user.
     *
     *  @since 7.4
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return bool
     */
    public function isUser(Content $content): bool;

    /**
     * Checks if Content is a user group.
     *
     * @since 7.4
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return bool
     */
    public function isUserGroup(Content $content): bool;

    /**
     * Instantiate a user create class.
     *
     * @param string $login the login of the new user
     * @param string $email the email of the new user
     * @param string $password the plain password of the new user
     * @param string $mainLanguageCode the main language for the underlying content object
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserCreateStruct
     */
    public function newUserCreateStruct(string $login, string $email, string $password, string $mainLanguageCode, ?ContentType $contentType = null): \eZ\Publish\API\Repository\Values\User\UserCreateStruct;

    /**
     * Instantiate a user group create class.
     *
     * @param string $mainLanguageCode The main language for the underlying content object
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType|null $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct
     */
    public function newUserGroupCreateStruct(string $mainLanguageCode, ?ContentType $contentType = null): UserGroupCreateStruct;

    /**
     * Instantiate a new user update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct(): UserUpdateStruct;

    /**
     * Instantiate a new user group update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct(): UserGroupUpdateStruct;

    /**
     * Validates given password.
     *
     * @param string $password
     * @param \eZ\Publish\API\Repository\Values\User\PasswordValidationContext|null $context
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validatePassword(string $password, PasswordValidationContext $context = null): array;

    /**
     * Returns information about password for a given user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\User\PasswordInfo
     */
    public function getPasswordInfo(User $user): PasswordInfo;
}
