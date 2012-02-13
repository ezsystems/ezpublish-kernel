<?php
/**
 * @package eZ\Publish\Core\Repository
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\Core\Repository\Values\User\UserCreateStruct,
    eZ\Publish\API\Repository\Values\User\UserCreateStruct as APIUserCreateStruct,
    eZ\Publish\API\Repository\Values\User\UserUpdateStruct,
    eZ\Publish\Core\Repository\Values\User\User,
    eZ\Publish\API\Repository\Values\User\User as APIUser,
    eZ\Publish\Core\Repository\Values\User\UserGroup,
    eZ\Publish\API\Repository\Values\User\UserGroup as APIUserGroup,
    eZ\Publish\Core\Repository\Values\User\UserGroupCreateStruct,
    eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct as APIUserGroupCreateStruct,
    eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct,

    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\API\Repository\UserService as UserServiceInterface,

    eZ\Publish\SPI\Persistence\User as SPIUser,

    ezp\Base\Exception\NotFound,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\IllegalArgumentException,
    eZ\Publish\Core\Base\Exceptions\BadStateException;

/**
 * This service provides methods for managing users and user groups
 *
 * @example Examples/user.php
 *
 * @package eZ\Publish\Core\Repository
 */
class UserService implements UserServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the input structure has invalid data
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userGroupCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     */
    public function createUserGroup( APIUserGroupCreateStruct $userGroupCreateStruct, APIUserGroup $parentGroup )
    {
        if ( empty( $parentGroup->id ) )
            throw new InvalidArgumentValue( "id", $parentGroup->id, "UserGroup" );

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $loadedParentGroup = $this->loadUserGroup( $parentGroup->id );
        $mainParentGroupLocation = $locationService->loadMainLocation( $loadedParentGroup->contentInfo );

        $locationCreateStructs = array();
        if ( $mainParentGroupLocation !== null )
        {
            $locationCreateStruct = $locationService->newLocationCreateStruct( $mainParentGroupLocation->id );
            $locationCreateStruct->isMainLocation = true;
            $locationCreateStructs[] = $locationCreateStruct;
        }

        $contentDraft = $contentService->createContent( $userGroupCreateStruct->contentCreateStruct, $locationCreateStructs );
        $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );

        return new UserGroup( array(
            'id'            => $publishedContent->contentId,
            'parentId'      => $mainParentGroupLocation !== null ? $mainParentGroupLocation->id : null,
            // @todo: calculate sub group count
            'subGroupCount' => 0,
            'content'       => $publishedContent
        ) );
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
        if ( empty( $id ) )
            throw new InvalidArgumentValue( "id", $id );

        $locationService = $this->repository->getLocationService();

        $content = $this->repository->getContentService()->loadContent( $id );
        $mainLocation = $locationService->loadMainLocation( $content->contentInfo );

        return new UserGroup( array(
            'id'            => $content->contentId,
            'parentId'      => $mainLocation !== null ? $mainLocation->parentId : null,
            //@todo: calculate sub group count
            'subGroupCount' => 0,
            'content'       => $content
        ) );
    }

    /**
     * Loads the sub groups of a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\UserGroup}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function loadSubUserGroups( APIUserGroup $userGroup ){}

    /**
     * Removes a user group
     *
     * the users which are not assigned to other groups will be deleted.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function deleteUserGroup( APIUserGroup $userGroup )
    {
        if ( empty( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $userGroup = $this->loadUserGroup( $userGroup->id );

        $this->repository->getContentService()->deleteContent( $userGroup->contentInfo );
    }

    /**
     * Moves the user group to another parent
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function moveUserGroup( APIUserGroup $userGroup, APIUserGroup $newParent ){}

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
     */
    public function updateUserGroup( APIUserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct ){}

    /**
     * Create a new user. The created user is published by this method
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserCreateStruct $userCreateStruct the data used for creating the user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup[] $parentGroups the groups which are assigned to the user after creation
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user group was not found
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     */
    public function createUser( APIUserCreateStruct $userCreateStruct, array $parentGroups )
    {
        if ( empty( $parentGroups ) )
            throw new InvalidArgumentValue( "parentGroups", $parentGroups );

        if ( empty( $userCreateStruct->login ) )
            throw new InvalidArgumentValue( "login", $userCreateStruct->login, "UserCreateStruct" );

        if ( empty( $userCreateStruct->email ) )
            throw new InvalidArgumentValue( "email", $userCreateStruct->email, "UserCreateStruct" );

        if ( empty( $userCreateStruct->password ) )
            throw new InvalidArgumentValue( "password", $userCreateStruct->password, "UserCreateStruct" );

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $locationCreateStructs = array();
        foreach ( $parentGroups as $parentGroup )
        {
            $parentGroup = $this->loadUserGroup( $parentGroup->id );
            $mainLocation = $locationService->loadMainLocation( $parentGroup->contentInfo );
            if ( $mainLocation !== null )
            {
                $locationCreateStruct = $locationService->newLocationCreateStruct( $mainLocation->id );
                $locationCreateStruct->isMainLocation = true;
                $locationCreateStructs[] = $locationCreateStruct;
            }
        }

        $contentDraft = $contentService->createContent( $userCreateStruct->contentCreateStruct, $locationCreateStructs );
        $contentService->publishVersion( $contentDraft->getVersionInfo() );

        $spiUser = $this->buildPersistenceUserObject( $userCreateStruct );
        $spiUser = $this->persistenceHandler->userHandler()->create( $spiUser );
        return $this->buildDomainUserObject( $spiUser );
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
        if ( empty( $userId ) )
            throw new InvalidArgumentValue( "userId", $userId );

        try
        {
            $spiUser = $this->persistenceHandler->userHandler()->load( $userId );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "user", $userId, $e );
        }

        return $this->buildDomainUserObject( $spiUser );
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
        if ( empty( $login ) )
            throw new InvalidArgumentValue( "login", $login );

        if ( empty( $password ) )
            throw new InvalidArgumentValue( "password", $password );

        try
        {
            $spiUser = $this->persistenceHandler->userHandler()->loadByLogin( $login );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "user", $login, $e );
        }

        // @todo: read site name from settings
        if ( $spiUser->passwordHash !== $this->createPasswordHash( $login, $password, null, $spiUser->hashAlgorithm ) )
            throw new InvalidArgumentValue( "password", $password );

        return $this->buildDomainUserObject( $spiUser );
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
        if ( empty( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        $user = $this->loadUser( $user->id );

        $this->repository->getContentService()->deleteContent( $user->contentInfo );
    }

    /**
     * Updates a user
     *
     * 4.x: If the versionUpdateStruct is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explicitly required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     */
    public function updateUser( APIUser $user, UserUpdateStruct $userUpdateStruct ){}

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
    public function assignUserToUserGroup( APIUser $user, APIUserGroup $userGroup )
    {
        if ( empty( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( empty( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $loadedUser = $this->loadUser( $user->id );
        $loadedGroup = $this->loadUserGroup( $userGroup->id );
        $locationService = $this->repository->getLocationService();

        $existingGroupIds = array();
        $userLocations = $locationService->loadLocations( $loadedUser->contentInfo );
        foreach ( $userLocations as $userLocation )
        {
            $existingGroupIds[] = $userLocation->parentId;
        }

        $groupLocations = $locationService->loadLocations( $loadedGroup->contentInfo );
        if ( empty( $groupLocations ) )
            // user group has no locations, nowhere to assign user to
            // @todo: maybe throw BadStateException?
            return;

        $newGroupIds = array();
        $mainLocationId = 0;
        foreach ( $groupLocations as $groupLocation )
        {
            $newGroupIds[] = $groupLocation->id;

            if ( $groupLocation->id === $groupLocation->mainLocationId )
                $mainLocationId = $groupLocation->id;
        }

        if ( $mainLocationId === 0 )
            // user group has no main location
            // @todo: maybe throw BadStateException, or use first location from the list?
            return;

        if ( count( array_intersect( $existingGroupIds, $newGroupIds ) ) > 0 )
            // user is already below one of the locations of the user group, do nothing
            return;

        $locationCreateStruct = $locationService->newLocationCreateStruct( $mainLocationId );
        $locationService->createLocation( $loadedUser->contentInfo, $locationCreateStruct );
    }

    /**
     * Removes a user group from the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove the user group from the user
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the user is not in the given user group
     */
    public function unAssignUssrFromUserGroup( APIUser $user, APIUserGroup $userGroup )
    {
        if ( empty( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( empty( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $loadedUser = $this->loadUser( $user->id );
        $loadedGroup = $this->loadUserGroup( $userGroup->id );
        $locationService = $this->repository->getLocationService();

        $userLocations = $locationService->loadLocations( $loadedUser->contentInfo );
        if ( empty( $userLocations ) )
            // user has no locations, nothing to remove
            // @todo: maybe throw BadStateException?
            return;

        $groupLocations = $locationService->loadLocations( $loadedGroup->contentInfo );
        if ( empty( $groupLocations ) )
            // user group has no locations
            // @todo: maybe throw BadStateException?
            return;

        foreach ( $userLocations as $userLocation )
        {
            foreach ( $groupLocations as $groupLocation )
            {
                if ( $userLocation->parentId === $groupLocation->id )
                {
                    $locationService->deleteLocation( $userLocation );
                    return;
                }
            }
        }

        throw new IllegalArgumentException( '$userGroup', 'user is not in the given user group' );
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
        $contentCreateStruct = $this->repository->getContentService()->newContentCreateStruct( $contentType, $mainLanguageCode );

        return new UserCreateStruct( array(
            'login'               => $login,
            'email'               => $email,
            'password'            => $password,
            'contentCreateStruct' => $contentCreateStruct
        ) );
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
        $contentCreateStruct = $this->repository->getContentService()->newContentCreateStruct( $contentType, $mainLanguageCode );

        return new UserGroupCreateStruct( array(
            'contentCreateStruct' => $contentCreateStruct
        ) );
    }

    /**
     * Instantiate a new user update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct()
    {
        $contentUpdateStruct = $this->repository->getContentService()->newContentUpdateStruct();

        return new UserUpdateStruct( array(
            'contentUpdateStruct' => $contentUpdateStruct
        ) );
    }

    /**
     * Instantiate a new user group update struct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct()
    {
        $contentUpdateStruct = $this->repository->getContentService()->newContentUpdateStruct();

        return new UserGroupUpdateStruct( array(
            'contentUpdateStruct' => $contentUpdateStruct
        ) );
    }

    protected function buildDomainUserObject( SPIUser $spiUser )
    {
        $content = $this->repository->getContentService()->loadContent( $spiUser->id );

        return new User( array(
            'id'            => $spiUser->id,
            'login'         => $spiUser->login,
            'email'         => $spiUser->email,
            'passwordHash'  => $spiUser->passwordHash,
            'hashAlgorithm' => $spiUser->hashAlgorithm,
            'isEnabled'     => $spiUser->isEnabled,
            'maxLogin'      => $spiUser->maxLogin,
            'content'       => $content
        ) );
    }

    protected function buildPersistenceUserObject( APIUserCreateStruct $userCreateStruct )
    {
        return new SPIUser( array(
            'login'         => $userCreateStruct->login,
            'email'         => $userCreateStruct->email,
            'password'      => $userCreateStruct->password,
            // @todo: read password hash algorithm and site from INI settings
            'passwordHash'  => $this->createPasswordHash( $userCreateStruct->login, $userCreateStruct->password, null, null ),
            'hashAlgorithm' => null,
            'isEnabled'     => $userCreateStruct->enabled
        ) );
    }

    protected function createPasswordHash( $login, $password, $site, $type )
    {
        $passwordHash = "";

        switch ( $type )
        {
            case User::PASSWORD_HASH_MD5_PASSWORD :
            {
                $passwordHash = md5( $password );
            }
            break;
            case User::PASSWORD_HASH_MD5_USER :
            {
                $passwordHash = md5( "$login\n$password" );
            }
            break;
            case User::PASSWORD_HASH_MD5_SITE :
            {
                $passwordHash = md5( "$login\n$password\n$site" );
            }
            break;
            case User::PASSWORD_HASH_PLAINTEXT :
            {
                $passwordHash = $password;
            }
            break;
            default :
            {
                $passwordHash = md5( $password );
            }
        }

        return $passwordHash;
    }
}
