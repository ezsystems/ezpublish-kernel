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
    eZ\Publish\API\Repository\Values\Content\Location,

    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\API\Repository\UserService as UserServiceInterface,

    eZ\Publish\SPI\Persistence\User as SPIUser,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId as CriterionContentTypeId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Status as CriterionStatus,

    ezp\Base\Exception\NotFound,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\IllegalArgumentException,
    eZ\Publish\Core\Base\Exceptions\BadStateException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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
        if ( !is_numeric( $parentGroup->id ) )
            throw new InvalidArgumentValue( "id", $parentGroup->id, "UserGroup" );

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $loadedParentGroup = $this->loadUserGroup( $parentGroup->id );
        $mainParentGroupLocation = $locationService->loadMainLocation( $loadedParentGroup->getVersionInfo()->getContentInfo() );

        if ( $mainParentGroupLocation === null )
            throw new IllegalArgumentException( "parentGroup", "parent user group has no main location" );

        $locationCreateStruct = $locationService->newLocationCreateStruct( $mainParentGroupLocation->id );
        $locationCreateStruct->isMainLocation = true;

        $contentDraft = $contentService->createContent( $userGroupCreateStruct, array( $locationCreateStruct ) );
        $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );
        $publishedContentInfo = $publishedContent->getVersionInfo()->getContentInfo();

        return new UserGroup( array(
            'contentInfo'   => $publishedContentInfo,
            'contentType'   => $publishedContentInfo->getContentType(),
            'contentId'     => $publishedContentInfo->contentId,
            'versionInfo'   => $publishedContent->getVersionInfo(),
            'fields'        => $publishedContent->getFields(),
            'relations'     => $publishedContent->getRelations(),
            'id'            => $publishedContentInfo->contentId,
            'parentId'      => $mainParentGroupLocation->id,
            'subGroupCount' => 0
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
        if ( !is_numeric( $id ) )
            throw new InvalidArgumentValue( "id", $id );

        $locationService = $this->repository->getLocationService();

        $content = $this->repository->getContentService()->loadContent( $id );
        $contentInfo = $content->getVersionInfo()->getContentInfo();
        $mainLocation = $locationService->loadMainLocation( $contentInfo );

        $subGroupCount = 0;
        if ( $mainLocation !== null )
        {
            $subGroups = $this->searchSubGroups( $mainLocation );
            $subGroupCount = $subGroups->count;
        }

        return new UserGroup( array(
            'contentInfo'   => $contentInfo,
            'contentType'   => $contentInfo->getContentType(),
            'contentId'     => $contentInfo->contentId,
            'versionInfo'   => $content->getVersionInfo(),
            'fields'        => $content->getFields(),
            'relations'     => $content->getRelations(),
            'id'            => $contentInfo->contentId,
            'parentId'      => $mainLocation !== null ? $mainLocation->parentId : null,
            'subGroupCount' => $subGroupCount
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
    public function loadSubUserGroups( APIUserGroup $userGroup )
    {
        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $locationService = $this->repository->getLocationService();

        $loadedUserGroup = $this->loadUserGroup( $userGroup->id );
        $mainGroupLocation = $locationService->loadMainLocation( $loadedUserGroup->getVersionInfo()->getContentInfo() );

        if ( $mainGroupLocation === null )
            return array();

        $searchResult = $this->searchSubGroups( $mainGroupLocation );
        if ( !is_array( $searchResult->items ) || empty( $searchResult->items ) )
            return array();

        $subUserGroups = array();
        foreach ( $searchResult->items as $resultItem )
        {
            $resultItemContentInfo = $resultItem->getVersionInfo()->getContentInfo();
            $subSubGroupMainLocation = $locationService->loadMainLocation( $resultItemContentInfo );
            $subSubGroups = $this->searchSubGroups( $subSubGroupMainLocation );

            /** @var \eZ\Publish\API\Repository\Values\Content\Content $resultItem */
            $subUserGroups[] = new UserGroup( array(
                'contentInfo'   => $resultItemContentInfo,
                'contentType'   => $resultItemContentInfo->getContentType(),
                'contentId'     => $resultItemContentInfo->contentId,
                'versionInfo'   => $resultItem->getVersionInfo(),
                'fields'        => $resultItem->getFields(),
                'relations'     => $resultItem->getRelations(),
                'id'            => $resultItemContentInfo->contentId,
                'parentId'      => $mainGroupLocation->id,
                'subGroupCount' => $subSubGroups->count
            ) );
        }

        return $subUserGroups;
    }

    protected function searchSubGroups( Location $location )
    {
        $searchQuery = new Query();
        $searchQuery->criterion = new CriterionLogicalAnd( array(
            //@todo: read user group type ID from INI settings
            new CriterionContentTypeId( 3 ),
            new CriterionParentLocationId( $location->id ),
            new CriterionStatus( CriterionStatus::STATUS_PUBLISHED )
        ) );

        return $this->repository->getContentService()->findContent( $searchQuery, array() );
    }

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
        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $loadedUserGroup = $this->loadUserGroup( $userGroup->id );

        //@todo: what happens to sub user groups and users below sub user groups

        $this->repository->getContentService()->deleteContent( $loadedUserGroup->getVersionInfo()->getContentInfo() );
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
    public function moveUserGroup( APIUserGroup $userGroup, APIUserGroup $newParent )
    {
        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        if ( !is_numeric( $newParent->id ) )
            throw new InvalidArgumentValue( "id", $newParent->id, "UserGroup" );

        $loadedUserGroup = $this->loadUserGroup( $userGroup->id );
        $loadedNewParent = $this->loadUserGroup( $newParent->id );

        $locationService = $this->repository->getLocationService();

        $userGroupMainLocation = $locationService->loadMainLocation( $loadedUserGroup->getVersionInfo()->getContentInfo() );
        $newParentMainLocation = $locationService->loadMainLocation( $loadedNewParent->getVersionInfo()->getContentInfo() );

        if ( $userGroupMainLocation === null )
            throw new BadStateException( "userGroup" );

        if ( $newParentMainLocation === null )
            throw new BadStateException( "newParent" );

        $this->repository->getLocationService()->moveSubtree( $userGroupMainLocation, $newParentMainLocation );
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
        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $loadedUserGroup = $this->loadUserGroup( $userGroup->id );

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentDraft = $contentService->createContentDraft( $loadedUserGroup->getVersionInfo()->getContentInfo() );
        $contentDraft = $contentService->updateContent( $contentDraft->getVersionInfo(), $userGroupUpdateStruct->contentUpdateStruct );

        $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );
        $publishedContentInfo = $publishedContent->getVersionInfo()->getContentInfo();

        $publishedContent = $contentService->updateContentMetadata( $publishedContentInfo, $userGroupUpdateStruct->contentMetaDataUpdateStruct );
        $publishedContentInfo = $publishedContent->getVersionInfo()->getContentInfo();

        $mainLocation = $locationService->loadMainLocation( $publishedContentInfo );

        $subGroupCount = 0;
        if ( $mainLocation !== null )
        {
            $subGroups = $this->searchSubGroups( $mainLocation );
            $subGroupCount = $subGroups->count;
        }

        return new UserGroup( array(
            'contentInfo'   => $publishedContentInfo,
            'contentType'   => $publishedContentInfo->getContentType(),
            'contentId'     => $publishedContentInfo->contentId,
            'versionInfo'   => $publishedContent->getVersionInfo(),
            'fields'        => $publishedContent->getFields(),
            'relations'     => $publishedContent->getRelations(),
            'id'            => $publishedContentInfo->contentId,
            'parentId'      => $mainLocation !== null ? $mainLocation->parentId : null,
            'subGroupCount' => $subGroupCount
        ) );
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user group was not found
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     */
    public function createUser( APIUserCreateStruct $userCreateStruct, array $parentGroups )
    {
        if ( empty( $parentGroups ) )
            throw new InvalidArgumentValue( "parentGroups", $parentGroups );

        if ( !is_string( $userCreateStruct->login ) || empty( $userCreateStruct->login ) )
            throw new InvalidArgumentValue( "login", $userCreateStruct->login, "UserCreateStruct" );

        //@todo: verify email validity
        if ( !is_string( $userCreateStruct->email ) || empty( $userCreateStruct->email ) )
            throw new InvalidArgumentValue( "email", $userCreateStruct->email, "UserCreateStruct" );

        if ( !is_string( $userCreateStruct->password ) || empty( $userCreateStruct->password ) )
            throw new InvalidArgumentValue( "password", $userCreateStruct->password, "UserCreateStruct" );

        if ( !is_bool( $userCreateStruct->enabled ) )
            throw new InvalidArgumentValue( "enabled", $userCreateStruct->enabled, "UserCreateStruct" );

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $locationCreateStructs = array();
        $isMainLocationSet = false;
        foreach ( $parentGroups as $parentGroup )
        {
            $parentGroup = $this->loadUserGroup( $parentGroup->id );
            $mainLocation = $locationService->loadMainLocation( $parentGroup->getVersionInfo()->getContentInfo() );
            if ( $mainLocation !== null )
            {
                $locationCreateStruct = $locationService->newLocationCreateStruct( $mainLocation->id );
                $locationCreateStruct->isMainLocation = !$isMainLocationSet;
                $locationCreateStructs[] = $locationCreateStruct;
                $isMainLocationSet = true;
            }
        }

        $contentDraft = $contentService->createContent( $userCreateStruct, $locationCreateStructs );
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
        if ( !is_numeric( $userId ) )
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
        if ( !is_string( $login ) || empty( $login ) )
            throw new InvalidArgumentValue( "login", $login );

        if ( !is_string( $password ) || empty( $password ) )
            throw new InvalidArgumentValue( "password", $password );

        try
        {
            $spiUsers = $this->persistenceHandler->userHandler()->loadByLogin( $login );
        }
        catch ( NotFound $e )
        {
            throw new NotFoundException( "user", $login, $e );
        }

        if ( count( $spiUsers ) > 1 )
        {
            // something went wrong, we should not have more than one
            // user with the same login
            throw new InvalidArgumentException( "login", "there are multiple users with the same login" );
        }

        // @todo: read site name from settings
        if ( $spiUsers[0]->passwordHash !== $this->createPasswordHash( $login, $password, 'eZ Publish', $spiUsers[0]->hashAlgorithm ) )
            throw new InvalidArgumentValue( "password", $password );

        return $this->buildDomainUserObject( $spiUsers[0] );
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
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        $user = $this->loadUser( $user->id );

        $this->repository->getContentService()->deleteContent( $user->getVersionInfo()->getContentInfo() );
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
    public function updateUser( APIUser $user, UserUpdateStruct $userUpdateStruct )
    {
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        //@todo: verify email validity
        if ( $userUpdateStruct->email !== null && ( !is_string( $userUpdateStruct->email ) || empty( $userUpdateStruct->email ) ) )
            throw new InvalidArgumentValue( "email", $userUpdateStruct->email, "UserUpdateStruct" );

        if ( $userUpdateStruct->password !== null && ( !is_string( $userUpdateStruct->password ) || empty( $userUpdateStruct->password ) ) )
            throw new InvalidArgumentValue( "password", $userUpdateStruct->password, "UserUpdateStruct" );

        if ( $userUpdateStruct->isEnabled !== null && !is_bool( $userUpdateStruct->isEnabled ) )
            throw new InvalidArgumentValue( "isEnabled", $userUpdateStruct->isEnabled, "UserUpdateStruct" );

        if ( $userUpdateStruct->maxLogin !== null && !is_numeric( $userUpdateStruct->maxLogin ) )
            throw new InvalidArgumentValue( "maxLogin", $userUpdateStruct->maxLogin, "UserUpdateStruct" );

        $contentService = $this->repository->getContentService();

        $loadedUser = $this->loadUser( $user->id );

        $contentDraft = $contentService->createContentDraft( $loadedUser->getVersionInfo()->getContentInfo() );
        $contentDraft = $contentService->updateContent( $contentDraft->getVersionInfo(), $userUpdateStruct->contentUpdateStruct );
        $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );

        $contentService->updateContentMetadata( $publishedContent->getVersionInfo()->getContentInfo(), $userUpdateStruct->contentMetaDataUpdateStruct );

        $this->persistenceHandler->userHandler()->update( new SPIUser( array(
            'id'            => $loadedUser->id,
            'login'         => $loadedUser->login,
            'email'         => $userUpdateStruct->email !== null ? trim( $userUpdateStruct->email ) : $loadedUser->email,
            // @todo: read password hash algorithm and site from INI settings
            'passwordHash'  => $userUpdateStruct->password !== null ?
                $this->createPasswordHash( $loadedUser->login, $userUpdateStruct->password, 'eZ Publish', User::PASSWORD_HASH_MD5_USER ) :
                $loadedUser->passwordHash,
            'hashAlgorithm' => null,
            'isEnabled'     => $userUpdateStruct->isEnabled !== null ? $userUpdateStruct->isEnabled : $loadedUser->isEnabled,
            'maxLogin'      => $userUpdateStruct->maxLogin !== null ? (int) $userUpdateStruct->maxLogin : $loadedUser->maxLogin
        ) ) );
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
    public function assignUserToUserGroup( APIUser $user, APIUserGroup $userGroup )
    {
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( !is_numeric( $userGroup->id ) )
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

        $groupMainLocation = $locationService->loadMainLocation( $loadedGroup->getVersionInfo()->getContentInfo() );
        if ( $groupMainLocation === null )
            throw new IllegalArgumentException( "userGroup", "user group has no main location or no locations" );

        if ( in_array( $groupMainLocation->id, $existingGroupIds ) )
            // user is already assigned to the user group
            return;

        $locationCreateStruct = $locationService->newLocationCreateStruct( $groupMainLocation->id );
        $locationService->createLocation( $loadedUser->getVersionInfo()->getContentInfo(), $locationCreateStruct );
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
    public function unAssignUserFromUserGroup( APIUser $user, APIUserGroup $userGroup )
    {
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $loadedUser = $this->loadUser( $user->id );
        $loadedGroup = $this->loadUserGroup( $userGroup->id );
        $locationService = $this->repository->getLocationService();

        $userLocations = $locationService->loadLocations( $loadedUser->contentInfo );
        if ( empty( $userLocations ) )
            throw new IllegalArgumentException( "user", "user has no locations, cannot unassign from group" );

        $groupMainLocation = $locationService->loadMainLocation( $loadedGroup->getVersionInfo()->getContentInfo() );
        if ( $groupMainLocation === null )
            throw new IllegalArgumentException( "userGroup", "user group has no main location or no locations, cannot unassign" );

        foreach ( $userLocations as $userLocation )
        {
            if ( $userLocation->parentId === $groupMainLocation->id )
            {
                $locationService->deleteLocation( $userLocation );
                return;
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
        return new UserCreateStruct( array(
            'contentType'      => $contentType,
            'mainLanguageCode' => $mainLanguageCode,
            'login'            => $login,
            'email'            => $email,
            'password'         => $password,
            'enabled'          => true,
            'fields'           => array(),
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
        return new UserGroupCreateStruct( array(
            'contentType'      => $contentType,
            'mainLanguageCode' => $mainLanguageCode,
            'fields'           => array(),
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
        $contentInfo = $content->getVersionInfo()->getContentInfo();

        return new User( array(
            'contentInfo'   => $contentInfo,
            'contentType'   => $contentInfo->getContentType(),
            'contentId'     => $contentInfo->contentId,
            'versionInfo'   => $content->getVersionInfo(),
            'fields'        => $content->getFields(),
            'relations'     => $content->getRelations(),
            'id'            => $spiUser->id,
            'login'         => $spiUser->login,
            'email'         => $spiUser->email,
            'passwordHash'  => $spiUser->passwordHash,
            'hashAlgorithm' => $spiUser->hashAlgorithm,
            'isEnabled'     => $spiUser->isEnabled,
            'maxLogin'      => $spiUser->maxLogin,
        ) );
    }

    protected function buildPersistenceUserObject( APIUserCreateStruct $userCreateStruct )
    {
        return new SPIUser( array(
            'login'         => $userCreateStruct->login,
            'email'         => $userCreateStruct->email,
            // @todo: read password hash algorithm and site from INI settings
            'passwordHash'  => $this->createPasswordHash( $userCreateStruct->login, $userCreateStruct->password, 'eZ Publish', User::PASSWORD_HASH_MD5_USER ),
            'hashAlgorithm' => null,
            'isEnabled'     => $userCreateStruct->enabled
        ) );
    }

    /**
     * Returns password hash based on user data and site settings
     *
     * @param string $login User login
     * @param string $password User password
     * @param string $site The name of the site
     * @param int $type Type of password to generate
     *
     * @return string Generated password hash
     */
    protected function createPasswordHash( $login, $password, $site, $type )
    {
        switch ( $type )
        {
            case User::PASSWORD_HASH_MD5_PASSWORD:
                return md5( $password );

            case User::PASSWORD_HASH_MD5_USER:
                return md5( "$login\n$password" );

            case User::PASSWORD_HASH_MD5_SITE:
                return md5( "$login\n$password\n$site" );

            case User::PASSWORD_HASH_PLAINTEXT:
                return $password;

            default:
                return md5( $password );
        }
    }
}
