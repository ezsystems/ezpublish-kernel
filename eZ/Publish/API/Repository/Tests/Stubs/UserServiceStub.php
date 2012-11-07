<?php
/**
 * File containing the UserServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\UserService;
use \eZ\Publish\API\Repository\Values\User\User;
use \eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use \eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use \eZ\Publish\API\Repository\Values\User\UserGroup;
use \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;

use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserCreateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupCreateStructStub;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\UserService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\UserService
 */
class UserServiceStub implements UserService
{
    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User[]
     */
    private $users;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    private $userGroups;

    /**
     * @var array
     */
    private $user2groups = array();

    /**
     * Instantiates a new user service instance.
     *
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;

        $this->initFromFixture();
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
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     */
    public function createUserGroup( UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup )
    {
        if ( false === $this->repository->canUser( 'content', 'create', $parentGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $locationCreate = $locationService->newLocationCreateStruct(
            $parentGroup->contentInfo->mainLocationId
        );

        $draft = $contentService->createContent(
            $userGroupCreateStruct,
            array( $locationCreate )
        );

        $content = $contentService->publishVersion( $draft->getVersionInfo() );

        $userGroup = new UserGroupStub(
            array(
                'parentId' => $parentGroup->id,
                'subGroupCount' => 0,
                'content' => $content
            )
        );
        $this->userGroups[$userGroup->id] = $userGroup;
        $this->userGroups[$parentGroup->id] = new UserGroupStub(
            array(
                'parentId' => $parentGroup->parentId,
                'subGroupCount' => $parentGroup->subGroupCount + 1,
                'content' => $parentGroup->content
            )
        );

        return $userGroup;
    }

    /**
     * Loads a user group for the given id
     *
     * @param int $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function loadUserGroup( $id )
    {
        if ( false === isset( $this->userGroups[$id] ) )
        {
            throw new NotFoundExceptionStub( 'What error code should be used?' );
        }
        if ( false === $this->repository->canUser( 'content', 'read', $this->userGroups[$id] ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return $this->userGroups[$id];
    }

    /**
     * Loads the sub groups of a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\UserGroup}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the user group
     */
    public function loadSubUserGroups( UserGroup $userGroup )
    {
        if ( false === $this->repository->canUser( 'content', 'read', $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $subUserGroups = array();
        foreach ( $this->userGroups as $group )
        {
            if ( (string) $group->parentId === (string) $userGroup->id )
            {
                $subUserGroups[] = $group;
            }
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
    public function deleteUserGroup( UserGroup $userGroup )
    {
        if ( false === $this->repository->canUser( 'content', 'remove', $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        foreach ( array_keys( $this->user2groups ) as $userId )
        {
            unset( $this->user2groups[$userId][$userGroup->id] );
        }
        unset( $this->userGroups[$userGroup->id] );
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
        if ( false === $this->repository->canUser( 'content', 'move', $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $contentService = $this->repository->getContentService();

        $oldParent = $this->userGroups[$userGroup->parentId];

        $this->userGroups[$oldParent->id] = new UserGroupStub(
            array(
                'parentId' => $oldParent->parentId,
                'subGroupCount' => $oldParent->subGroupCount - 1,
                'content' => $contentService->loadContent(
                    $oldParent->id
                )
            )
        );

        $this->userGroups[$userGroup->id] = new UserGroupStub(
            array(
                'parentId' => $newParent->id,
                'subGroupCount' => $userGroup->subGroupCount,
                'content' => $contentService->loadContent(
                    $userGroup->id
                )
            )
        );

        $this->userGroups[$newParent->id] = new UserGroupStub(
            array(
                'parentId' => $newParent->parentId,
                'subGroupCount' => $newParent->subGroupCount + 1,
                'content' => $contentService->loadContent(
                    $newParent->id
                )
            )
        );
    }

    /**
     * Updates the group profile with fields and meta data
     *
     * 4.x: If the versionUpdateStruct is set in $userGroupUpdateStruct, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explititely required, the user group can be updated via the content service methods.
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
    public function updateUserGroup( UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $contentService = $this->repository->getContentService();

        $content = null;
        if ( $userGroupUpdateStruct->contentUpdateStruct )
        {
            $draft = $contentService->createContentDraft( $userGroup->contentInfo );
            $draft = $contentService->updateContent(
                $draft->getVersionInfo(),
                $userGroupUpdateStruct->contentUpdateStruct
            );

            $content = $contentService->publishVersion( $draft->getVersionInfo() );
        }

        if ( $userGroupUpdateStruct->contentMetadataUpdateStruct )
        {
            $content = $contentService->updateContentMetadata(
                $userGroup->contentInfo,
                $userGroupUpdateStruct->contentMetadataUpdateStruct
            );
        }

        if ( null !== $content )
        {
            $this->userGroups[$userGroup->id] = new UserGroupStub(
                array(
                    'parentId' => $userGroup->parentId,
                    'subGroupCount' => $userGroup->subGroupCount,
                    'content' => $content
                )
            );
        }

        return $this->userGroups[$userGroup->id];
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user group was not found
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a user with provided login already exists
     */
    public function createUser( UserCreateStruct $userCreateStruct, array $parentGroups )
    {
        if ( false === $this->repository->hasAccess( 'content', 'create' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        foreach ( $this->users as $user )
        {
            if ( $user->login == $userCreateStruct->login )
            {
                throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
            }
        }

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $locationCreateStruts = array();
        foreach ( $parentGroups as $parentGroup )
        {
            if ( false === $this->repository->canUser( 'content', 'edit', $parentGroup ) )
            {
                throw new UnauthorizedExceptionStub( 'What error code should be used?' );
            }

            $locationCreateStruts[] = $locationService->newLocationCreateStruct(
                $parentGroup->contentInfo->mainLocationId
            );
        }

        // Seems the is a back reference in the content object
        $userCreateStruct->setField( 'user_account', new UserStub() );

        $draft = $contentService->createContent(
            $userCreateStruct,
            $locationCreateStruts
        );
        $content = $contentService->publishVersion( $draft->getVersionInfo() );

        $user = new UserStub(
            array(
                'login' => $userCreateStruct->login,
                'email' => $userCreateStruct->email,
                'passwordHash' => $this->createHash(
                    $userCreateStruct->login,
                    $userCreateStruct->password,
                    2
                ),
                'hashAlgorithm' => 2,
                'enabled' => $userCreateStruct->enabled,
                'content' => $content
            )
        );
        $this->users[$user->id] = $user;

        foreach ( $parentGroups as $parentGroup )
        {
            $this->assignUserToUserGroup( $user, $parentGroup );
        }

        return $user;
    }

    /**
     * Loads a user
     *
     * @param integer $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     */
    public function loadUser( $userId )
    {
        if ( isset( $this->users[$userId] ) )
        {
            return $this->users[$userId];
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
    }

    /**
     * Loads anonymous user
     *
     * @uses loadUser()
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadAnonymousUser()
    {
        // 10 is the contentId of the anon user in the test dump
        return $this->loadUser( 10 );
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
        foreach ( $this->users as $user )
        {
            if ( $login !== $user->login )
            {
                continue;
            }

            $passwordHash = $this->createHash(
                $login,
                $password,
                $user->hashAlgorithm
            );

            if ( $passwordHash !== $user->passwordHash )
            {
                continue;
            }
            return $user;
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
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
        if ( false === $this->repository->canUser( 'content', 'remove', $user ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        unset(
            $this->users[$user->id],
            $this->user2groups[$user->id]
        );
    }

    /**
     * Updates a user
     *
     * 4.x: If the versionUpdateStruct is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explititely required, the user group can be updated via the content service methods.
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
    public function updateUser( User $user, UserUpdateStruct $userUpdateStruct )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $user ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $contentService = $this->repository->getContentService();

        $content = $contentService->loadContentByContentInfo( $user->contentInfo );

        $contentUpdate = $userUpdateStruct->contentUpdateStruct;
        if ( $contentUpdate === null )
        {
            $contentUpdate = $contentService->newContentUpdateStruct();
            foreach ( $content->getFields() as $field )
            {
                $contentUpdate->setField( $field->fieldDefIdentifier, $field->value, $field->languageCode );
            }
            $contentUpdate->setField( 'user_account', $user );
        }

        $contentDraft = $contentService->createContentDraft( $user->contentInfo );
        $contentDraft = $contentService->updateContent(
            $contentDraft->getVersionInfo(),
            $contentUpdate
        );

        $content = $contentService->publishVersion(
            $contentDraft->getVersionInfo()
        );

        if ( $userUpdateStruct->contentMetadataUpdateStruct )
        {
            $content = $contentService->updateContentMetadata(
                $content->contentInfo,
                $userUpdateStruct->contentMetadataUpdateStruct
            );
        }

        $this->users[$user->id] = new UserStub(
            array(
                'login' => $user->login,
                'email' => $userUpdateStruct->email ?: $user->email,
                'enabled' => is_null( $userUpdateStruct->enabled ) ? $user->enabled : $userUpdateStruct->enabled,
                'maxLogin' => is_null( $userUpdateStruct->maxLogin ) ? $user->maxLogin : $userUpdateStruct->maxLogin,
                'hashAlgorithm' => $user->hashAlgorithm,
                'passwordHash' => $userUpdateStruct->password ?
                    $this->createHash(
                        $user->login,
                        $userUpdateStruct->password,
                        $user->hashAlgorithm ) : $user->passwordHash,

                'content' => $content,
            )
        );

        return $this->users[$user->id];
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
        if ( false === $this->repository->canUser( 'content', 'edit', $user, $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( true === isset( $this->user2groups[$user->id][$userGroup->id] ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        if ( false === isset( $this->user2groups[$user->id] ) )
        {
            $this->user2groups[$user->id] = array();
        }
        $this->user2groups[$user->id][$userGroup->id] = true;
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
        if ( false === $this->repository->canUser( 'content', 'edit', $user, $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( false === isset( $this->user2groups[$user->id][$userGroup->id] ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }
        unset( $this->user2groups[$user->id][$userGroup->id] );
    }

    /**
     * Loads the user groups ther user belongs to
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed read the user or user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    public function loadUserGroupsOfUser( User $user)
    {
        if ( false === $this->repository->canUser( 'content', 'read', $user ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $userGroups = array();
        foreach ( array_keys( $this->user2groups[$user->id] ) as $groupId )
        {
            $userGroups[] = $this->userGroups[$groupId];
        }
        return $userGroups;
    }

    /**
     * loads the users of a user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the users or user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\User\User[]
     */
    public function loadUsersOfUserGroup( UserGroup $userGroup, $offset = 0, $limit = -1)
    {
        if ( false === $this->repository->canUser( 'content', 'read', $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $users = array();
        foreach ( $this->user2groups as $userId => $userGroupIds )
        {
            if ( isset( $userGroupIds[$userGroup->id] ) )
            {
                $users[] = $this->users[$userId];
            }
        }
        return $users;
    }

    /**
     * Internal helper method.
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    public function __loadUserGroupsByUserId( $userId )
    {
        if ( false === isset( $this->user2groups[$userId] ) )
        {
            return array();
        }

        $groupIds = array_keys( $this->user2groups[$userId] );
        $userGroups = array();
        while ( !empty( $groupIds ) )
        {
            $groupId = array_pop( $groupIds );

            if ( $this->userGroups[$groupId]->parentId > 0 )
            {
                $groupIds[] = $this->userGroups[$groupId]->parentId;
            }
            $userGroups[$groupId] = $this->userGroups[$groupId];
        }
        return $userGroups;
    }

    /**
     * Instantiate a user create class
     *
     * @paramb string $login the login of the new user
     * @param string $email the email of the new user
     * @param string $password the plain password of the new user
     * @param string $mainLanguageCode the main language for the underlying content object
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserCreateStruct
     */
    public function newUserCreateStruct( $login, $email, $password, $mainLanguageCode, $contentType = null )
    {
        $contentType = $contentType ?:
            $this->repository->getContentTypeService()->loadContentTypeByIdentifier( 'user' );

        return new UserCreateStructStub(
            array(
                'login' => $login,
                'email' => $email,
                'password' => $password,
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
                'remoteId' => md5( uniqid( __METHOD__, true ) )
            )
        );

    }

    /**
     * Instantiate a user group create class
     *
     * @param string $mainLanguageCode The main language for the underlying content object
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct
     */
    public function newUserGroupCreateStruct( $mainLanguageCode, $contentType = null )
    {
        $contentType = $contentType ?:
            $this->repository->getContentTypeService()->loadContentTypeByIdentifier( 'user_group' );

        return new UserGroupCreateStructStub(
            array(
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
                'remoteId' => md5( uniqid( __METHOD__, true ) )
            )
        );
    }

    /**
     * Instantiate a new user update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct()
    {
        return new UserUpdateStruct();
    }

    /**
     * Instantiate a new user group update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct()
    {
        return new UserGroupUpdateStruct();
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @return void
     */
    public function __rollback()
    {
        $this->initFromFixture();
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        list( $this->userGroups ) = $this->repository->loadFixture( 'UserGroup' );
        list( $this->users ) = $this->repository->loadFixture( 'User' );

        // Set the default relations.
        $this->user2groups[10] = array( 42 => true );
        $this->user2groups[14] = array( 12 => true );
    }

    private function createHash( $login, $password, $type )
    {
        switch ( $type )
        {
            case 2:
                /* PASSWORD_HASH_MD5_USER */
                return md5( "{$login}\n{$password}" );

            case 3:
                /* PASSWORD_HASH_MD5_SITE */
                $site = null;
                return md5( "{$login}\n{$password}\n{$site}" );

            case 5:
                /* PASSWORD_HASH_PLAINTEXT */
                return $password;
        }
        /* PASSWORD_HASH_MD5_PASSWORD (1) */
        return md5( $password );
    }
}
