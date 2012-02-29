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

use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
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
     * @var integer
     */
    private $userNextId;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    private $userGroups;

    /**
     * @var integer
     */
    private $userGroupNextId;

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
        $contentService  = $this->repository->getContentService();
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
                'id'             =>  $content->contentId,
                'parentId'       =>  $parentGroup->id,
                'subGroupCount'  =>  0,
                'content'        =>  $content
            )
        );
        $this->userGroups[$userGroup->id]   = $userGroup;
        $this->userGroups[$parentGroup->id] = new UserGroupStub(
            array(
                'id'             =>  $parentGroup->id,
                'parentId'       =>  $parentGroup->parentId,
                'subGroupCount'  =>  $parentGroup->subGroupCount + 1,
                'content'        =>  $parentGroup->content
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
        if ( isset( $this->userGroups[$id] ) )
        {
            return $this->userGroups[$id];
        }
        throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
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
        // TODO: Implement deleteUserGroup() method.
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
        // TODO: Implement moveUserGroup() method.
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
        // TODO: Implement updateUserGroup() method.
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
     */
    public function createUser( UserCreateStruct $userCreateStruct, array $parentGroups )
    {
        // TODO: Implement createUser() method.
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
        // TODO: Implement loadUser() method.
    }

    /**
     * Loads anonymous user
     *
     * @uses loadUser()
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadAnonymousUser()
    {
        // TODO: Implement loadAnonymousUser() method.
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
        // TODO: Implement loadUserByCredentials() method.
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
        // TODO: Implement deleteUser() method.
    }

    /**
     * Updates a user
     *
     * 4.x: If the versionUpdateStruct is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explititely required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     */
    public function updateUser( User $user, UserUpdateStruct $userUpdateStruct )
    {
        // TODO: Implement updateUser() method.
    }

    /**
     * Assigns a new user group to the user
     *
     * If the user is already in the given user group this method does nothing.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign the user group to the user
     */
    public function assignUserToUserGroup( User $user, UserGroup $userGroup )
    {
        // TODO: Implement assignUserToUserGroup() method.
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
        // TODO: Implement unAssignUssrFromUserGroup() method.
    }

    /**
     * Instantiate a user create class
     *
     * @paramb string $login the login of the new user
     * @param string $email the email of the new user
     * @param string $password the plain password of the new user
     * @param string $mainLanguageCode the main language for the underlying content object
     * @param ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserCreateStruct
     */
    public function newUserCreateStruct( $login, $email, $password, $mainLanguageCode, $contentType = null )
    {
        // TODO: Implement newUserCreateStruct() method.
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
                'mainLanguageCode'  =>  $mainLanguageCode,
                'contentType'       =>  $contentType
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
        // TODO: Implement newUserUpdateStruct() method.
    }

    /**
     * Instantiate a new user group update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct()
    {
        // TODO: Implement newUserGroupUpdateStruct() method.
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        list(
            $this->userGroups,
            $this->userGroupNextId
        ) = $this->repository->loadFixture( 'UserGroup' );

        list(
            $this->users,
            $this->userNextId
        ) = $this->repository->loadFixture( 'User' );
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
