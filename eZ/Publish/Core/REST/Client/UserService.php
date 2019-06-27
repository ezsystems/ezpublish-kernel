<?php

/**
 * File containing the UserService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\UserService as APIUserService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\UserService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\UserService
 */
class UserService implements APIUserService, Sessionable
{
    /** @var \eZ\Publish\Core\REST\Client\HttpClient */
    private $client;

    /** @var \eZ\Publish\Core\REST\Common\Input\Dispatcher */
    private $inputDispatcher;

    /** @var \eZ\Publish\Core\REST\Common\Output\Visitor */
    private $outputVisitor;

    /** @var \eZ\Publish\Core\REST\Common\RequestParser */
    private $requestParser;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     */
    public function __construct(HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, RequestParser $requestParser)
    {
        $this->client = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor = $outputVisitor;
        $this->requestParser = $requestParser;
    }

    /**
     * Set session ID.
     *
     * Only for testing
     *
     * @param mixed tringid
     *
     * @private
     */
    public function setSession($id)
    {
        if ($this->outputVisitor instanceof Sessionable) {
            $this->outputVisitor->setSession($id);
        }
    }

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
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     */
    public function createUserGroup(UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserGroup($id, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadSubUserGroups(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Removes a user group.
     *
     * the users which are not assigned to other groups will be deleted.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     */
    public function deleteUserGroup(UserGroup $userGroup)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Moves the user group to another parent.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     */
    public function moveUserGroup(UserGroup $userGroup, UserGroup $newParent)
    {
        throw new \Exception('@todo: Implement.');
    }

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
     */
    public function updateUserGroup(UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Create a new user. The created user is published by this method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserCreateStruct $userCreateStruct the data used for creating the user
     * @param array $parentGroups the groups of type {@link \eZ\Publish\API\Repository\Values\User\UserGroup} which are assigned to the user after creation
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user group was not found
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a user with provided login already exists
     */
    public function createUser(UserCreateStruct $userCreateStruct, array $parentGroups)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUser($userId, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Loads anonymous user.
     *
     * @deprecated since 5.3, use loadUser( $anonymousUserId ) instead
     *
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadAnonymousUser()
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByCredentials($login, $password, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByLogin($login, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUsersByEmail($email, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Loads a user with user hash key.
     *
     * @param string $hash
     * @param array $prioritizedLanguages
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadUserByToken($hash, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * This method deletes a user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete the user
     */
    public function deleteUser(User $user)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Updates a user.
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
    public function updateUser(User $user, UserUpdateStruct $userUpdateStruct)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Update the user token information specified by the user token struct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct $userTokenUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUserToken(User $user, UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Expires user token with user hash.
     *
     * @param string $hash
     */
    public function expireUserToken($hash)
    {
        throw new \Exception('@todo: Implement.');
    }


    /**
     * Assigns a new user group to the user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign the user group to the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is already in the given user group
     */
    public function assignUserToUserGroup(User $user, UserGroup $userGroup)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Removes a user group from the user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove the user group from the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is not in the given user group
     */
    public function unAssignUserFromUserGroup(User $user, UserGroup $userGroup)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserGroupsOfUser(User $user, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadUsersOfUserGroup(
        UserGroup $userGroup,
        $offset = 0,
        $limit = 25,
        array $prioritizedLanguages = []
    ) {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function isUser(Content $content): bool
    {
        foreach ($content->getFields() as $field) {
            if ($field->fieldTypeIdentifier === 'ezuser') {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isUserGroup(Content $content): bool
    {
        // TODO: Need a way to identify user groups her. Config is an option, lookup another but not an ideal solution.
        return false;
    }

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
    public function newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType = null)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Instantiate a user group create class.
     *
     * @param string $mainLanguageCode The main language for the underlying content object
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct
     */
    public function newUserGroupCreateStruct($mainLanguageCode, $contentType = null)
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Instantiate a new user update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct()
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * Instantiate a new user group update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct()
    {
        throw new \Exception('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword(string $password, PasswordValidationContext $context = null): array
    {
        throw new \Exception('@todo: Implement.');
    }
}
