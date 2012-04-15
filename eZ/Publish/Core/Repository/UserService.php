<?php
/**
 * File containing the eZ\Publish\Core\Repository\UserService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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
    eZ\Publish\API\Repository\Values\Content\Content,

    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\API\Repository\UserService as UserServiceInterface,

    eZ\Publish\SPI\Persistence\User as SPIUser,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId as CriterionContentTypeId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId as CriterionLocationId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Status as CriterionStatus,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\BadStateException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,

    ezcMailTools;

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
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository  $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings + array(
            'anonymousUserID' => 10,
            'defaultUserPlacement' => 12,
            'userClassID' => 4,
            'userGroupClassID' => 3,
            'hashType' => User::PASSWORD_HASH_MD5_USER,
            'siteName' => 'ez.no'
        );
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
        if ( !is_numeric( $parentGroup->id ) )
            throw new InvalidArgumentValue( "id", $parentGroup->id, "UserGroup" );

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();
        $contentTypeService = $this->repository->getContentTypeService();

        if ( $userGroupCreateStruct->contentType === null )
        {
            $userGroupContentType = $contentTypeService->loadContentType( $this->settings['userGroupClassID'] );
            $userGroupCreateStruct->contentType = $userGroupContentType;
        }

        $loadedParentGroup = $this->loadUserGroup( $parentGroup->id );
        $mainParentGroupLocation = $locationService->loadMainLocation( $loadedParentGroup->getVersionInfo()->getContentInfo() );

        if ( $mainParentGroupLocation === null )
            throw new InvalidArgumentException( "parentGroup", "parent user group has no main location" );

        $locationCreateStruct = $locationService->newLocationCreateStruct( $mainParentGroupLocation->id );
        $contentDraft = $contentService->createContent( $userGroupCreateStruct, array( $locationCreateStruct ) );
        $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );

        return $this->buildDomainUserGroupObject( $publishedContent );
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

        $content = $this->repository->getContentService()->loadContent( $id );

        return $this->buildDomainUserGroupObject( $content );
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
        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $locationService = $this->repository->getLocationService();

        $loadedUserGroup = $this->loadUserGroup( $userGroup->id );
        $mainGroupLocation = $locationService->loadMainLocation( $loadedUserGroup->getVersionInfo()->getContentInfo() );

        if ( $mainGroupLocation === null )
            return array();

        $searchResult = $this->searchSubGroups( $mainGroupLocation->id, $mainGroupLocation->sortField, $mainGroupLocation->sortOrder );
        if ( $searchResult->count == 0 )
            return array();

        $subUserGroups = array();
        foreach ( $searchResult->items as $resultItem )
        {
            $subUserGroups[] = $this->buildDomainUserGroupObject( $resultItem );
        }

        return $subUserGroups;
    }

    /**
     * Returns (searches) subgroups of a user group described by its main location
     *
     * @param int $locationId
     * @param int|null $sortField
     * @param int $sortOrder
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SearchResult
     */
    protected function searchSubGroups( $locationId, $sortField = null, $sortOrder = Location::SORT_ORDER_ASC, $offset = 0, $limit = -1 )
    {
        $searchQuery = new Query();

        $searchQuery->offset = $offset >= 0 ? (int) $offset : 0;
        $searchQuery->limit  = $limit  >= 0 ? (int) $limit  : null;

        $searchQuery->criterion = new CriterionLogicalAnd(
            array(
                new CriterionContentTypeId( $this->settings['userGroupClassID'] ),
                new CriterionParentLocationId( $locationId ),
                new CriterionStatus( CriterionStatus::STATUS_PUBLISHED )
            )
        );

        $sortClause = null;
        if ( $sortField !== null )
            $sortClause = array( $this->getSortClauseBySortField( $sortField, $sortOrder ) );

        $searchQuery->sortClauses = $sortClause;

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
            throw new BadStateException( "userGroup", 'existing user group is not stored and/or does not have any location yet' );

        if ( $newParentMainLocation === null )
            throw new BadStateException( "newParent", 'new user group is not stored and/or does not have any location yet' );

        $locationService->moveSubtree( $userGroupMainLocation, $newParentMainLocation );
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

        if ( $userGroupUpdateStruct->contentUpdateStruct === null &&
             $userGroupUpdateStruct->contentMetaDataUpdateStruct === null )
        {
            // both update structs are empty, nothing to do
            return $userGroup;
        }

        $contentService = $this->repository->getContentService();

        $loadedUserGroup = $this->loadUserGroup( $userGroup->id );

        $publishedContent = $loadedUserGroup;
        if ( $userGroupUpdateStruct->contentUpdateStruct !== null )
        {
            $contentDraft = $contentService->createContentDraft( $loadedUserGroup->getVersionInfo()->getContentInfo() );

            $contentDraft = $contentService->updateContent(
                $contentDraft->getVersionInfo(),
                $userGroupUpdateStruct->contentUpdateStruct
            );

            $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );
        }

        if ( $userGroupUpdateStruct->contentMetaDataUpdateStruct !== null )
        {
            $publishedContent = $contentService->updateContentMetadata(
                $publishedContent->getVersionInfo()->getContentInfo(),
                $userGroupUpdateStruct->contentMetaDataUpdateStruct
            );
        }

        return $this->buildDomainUserGroupObject( $publishedContent );
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
     */
    public function createUser( APIUserCreateStruct $userCreateStruct, array $parentGroups )
    {
        if ( empty( $parentGroups ) )
            throw new InvalidArgumentValue( "parentGroups", $parentGroups );

        if ( !is_string( $userCreateStruct->login ) || empty( $userCreateStruct->login ) )
            throw new InvalidArgumentValue( "login", $userCreateStruct->login, "UserCreateStruct" );

        if ( !is_string( $userCreateStruct->email ) || empty( $userCreateStruct->email ) )
            throw new InvalidArgumentValue( "email", $userCreateStruct->email, "UserCreateStruct" );

        if ( !ezcMailTools::validateEmailAddress( $userCreateStruct->email ) )
            throw new InvalidArgumentValue( "email", $userCreateStruct->email, "UserCreateStruct" );

        if ( !is_string( $userCreateStruct->password ) || empty( $userCreateStruct->password ) )
            throw new InvalidArgumentValue( "password", $userCreateStruct->password, "UserCreateStruct" );

        if ( !is_bool( $userCreateStruct->enabled ) )
            throw new InvalidArgumentValue( "enabled", $userCreateStruct->enabled, "UserCreateStruct" );

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();
        $contentTypeService = $this->repository->getContentTypeService();

        if ( $userCreateStruct->contentType === null )
        {
            $userContentType = $contentTypeService->loadContentType( $this->settings['userClassID'] );
            $userCreateStruct->contentType = $userContentType;
        }

        $locationCreateStructs = array();
        foreach ( $parentGroups as $parentGroup )
        {
            $parentGroup = $this->loadUserGroup( $parentGroup->id );
            $mainLocation = $locationService->loadMainLocation( $parentGroup->getVersionInfo()->getContentInfo() );
            if ( $mainLocation !== null )
                $locationCreateStructs[] = $locationService->newLocationCreateStruct( $mainLocation->id );
        }

        $contentDraft = $contentService->createContent( $userCreateStruct, $locationCreateStructs );
        $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );

        $spiUser = $this->persistenceHandler->userHandler()->create(
            new SPIUser(
                array(
                    'id'            => $publishedContent->contentId,
                    'login'         => $userCreateStruct->login,
                    'email'         => $userCreateStruct->email,
                    'passwordHash'  => $this->createPasswordHash(
                        $userCreateStruct->login,
                        $userCreateStruct->password,
                        $this->settings['siteName'],
                        $this->settings['hashType']
                    ),
                    'hashAlgorithm' => $this->settings['hashType'],
                    'isEnabled'     => $userCreateStruct->enabled,
                    'maxLogin'      => 0
                )
            )
        );

        return $this->buildDomainUserObject( $spiUser, $publishedContent );
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

        $spiUser = $this->persistenceHandler->userHandler()->load( $userId );
        return $this->buildDomainUserObject( $spiUser );
    }

    /**
     * Loads anonymous user
     *
     * @uses loadUser()
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadAnonymousUser()
    {
        return $this->loadUser( $this->settings['anonymousUserID'] );
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
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if multiple users with same login were found
     */
    public function loadUserByCredentials( $login, $password )
    {
        if ( !is_string( $login ) || empty( $login ) )
            throw new InvalidArgumentValue( "login", $login );

        if ( !is_string( $password ) || empty( $password ) )
            throw new InvalidArgumentValue( "password", $password );

        $spiUsers = $this->persistenceHandler->userHandler()->loadByLogin( $login );

        if ( empty( $spiUsers ) )
            throw new NotFoundException( "user", $login );

        if ( count( $spiUsers ) > 1 )
        {
            // something went wrong, we should not have more than one
            // user with the same login
            throw new BadStateException( "login", 'found several users with same login' );
        }

        $passwordHash = $this->createPasswordHash(
            $login,
            $password,
            $this->settings['siteName'],
            $spiUsers[0]->hashAlgorithm
        );

        if ( $spiUsers[0]->passwordHash !== $passwordHash )
            throw new NotFoundException( "user", $login );

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

        $loadedUser = $this->loadUser( $user->id );

        $this->repository->getContentService()->deleteContent( $loadedUser->getVersionInfo()->getContentInfo() );
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
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUser( APIUser $user, UserUpdateStruct $userUpdateStruct )
    {
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( $userUpdateStruct->email !== null )
        {
            if ( !is_string( $userUpdateStruct->email ) || empty( $userUpdateStruct->email ) )
                throw new InvalidArgumentValue( "email", $userUpdateStruct->email, "UserUpdateStruct" );

            if ( !ezcMailTools::validateEmailAddress( $userUpdateStruct->email ) )
                throw new InvalidArgumentValue( "email", $userUpdateStruct->email, "UserUpdateStruct" );
        }

        if ( $userUpdateStruct->password !== null && ( !is_string( $userUpdateStruct->password ) || empty( $userUpdateStruct->password ) ) )
            throw new InvalidArgumentValue( "password", $userUpdateStruct->password, "UserUpdateStruct" );

        if ( $userUpdateStruct->isEnabled !== null && !is_bool( $userUpdateStruct->isEnabled ) )
            throw new InvalidArgumentValue( "isEnabled", $userUpdateStruct->isEnabled, "UserUpdateStruct" );

        if ( $userUpdateStruct->maxLogin !== null && !is_numeric( $userUpdateStruct->maxLogin ) )
            throw new InvalidArgumentValue( "maxLogin", $userUpdateStruct->maxLogin, "UserUpdateStruct" );

        $contentService = $this->repository->getContentService();

        $loadedUser = $this->loadUser( $user->id );

        $publishedContent = $loadedUser;
        if ( $userUpdateStruct->contentUpdateStruct !== null )
        {
            $contentDraft = $contentService->createContentDraft( $loadedUser->getVersionInfo()->getContentInfo() );

            $contentDraft = $contentService->updateContent(
                $contentDraft->getVersionInfo(),
                $userUpdateStruct->contentUpdateStruct
            );

            $publishedContent = $contentService->publishVersion( $contentDraft->getVersionInfo() );
        }

        if ( $userUpdateStruct->contentMetaDataUpdateStruct !== null )
        {
            $contentService->updateContentMetadata(
                $publishedContent->getVersionInfo()->getContentInfo(),
                $userUpdateStruct->contentMetaDataUpdateStruct
            );
        }

        $this->persistenceHandler->userHandler()->update(
            new SPIUser(
                array(
                    'id'            => $loadedUser->id,
                    'login'         => $loadedUser->login,
                    'email'         => $userUpdateStruct->email ?: $loadedUser->email,
                    'passwordHash'  => $userUpdateStruct->password ?
                        $this->createPasswordHash(
                            $loadedUser->login,
                            $userUpdateStruct->password,
                            $this->settings['siteName'],
                            $this->settings['hashType']
                        ) :
                        $loadedUser->passwordHash,
                    'hashAlgorithm' => $this->settings['hashType'],
                    'isEnabled'     => $userUpdateStruct->isEnabled !== null ? $userUpdateStruct->isEnabled : $loadedUser->isEnabled,
                    'maxLogin'      => $userUpdateStruct->maxLogin !== null ? (int) $userUpdateStruct->maxLogin : $loadedUser->maxLogin
                )
            )
        );

        return $this->loadUser( $loadedUser->id );
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
        $userLocations = $locationService->loadLocations( $loadedUser->getVersionInfo()->getContentInfo() );
        foreach ( $userLocations as $userLocation )
        {
            $existingGroupIds[] = $userLocation->parentLocationId;
        }

        $groupMainLocation = $locationService->loadMainLocation( $loadedGroup->getVersionInfo()->getContentInfo() );
        if ( $groupMainLocation === null )
            throw new InvalidArgumentException( "userGroup", "user group has no main location or no locations" );

        if ( in_array( $groupMainLocation->id, $existingGroupIds ) )
            // user is already assigned to the user group
            return;

        $locationCreateStruct = $locationService->newLocationCreateStruct( $groupMainLocation->id );

        $locationService->createLocation(
            $loadedUser->getVersionInfo()->getContentInfo(),
            $locationCreateStruct
        );
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
    public function unAssignUserFromUserGroup( APIUser $user, APIUserGroup $userGroup )
    {
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        $loadedUser = $this->loadUser( $user->id );
        $loadedGroup = $this->loadUserGroup( $userGroup->id );
        $locationService = $this->repository->getLocationService();

        $userLocations = $locationService->loadLocations( $loadedUser->getVersionInfo()->getContentInfo() );
        if ( empty( $userLocations ) )
            throw new InvalidArgumentException( "user", "user has no locations, cannot unassign from group" );

        $groupMainLocation = $locationService->loadMainLocation( $loadedGroup->getVersionInfo()->getContentInfo() );
        if ( $groupMainLocation === null )
            throw new InvalidArgumentException( "userGroup", "user group has no main location or no locations, cannot unassign" );

        foreach ( $userLocations as $userLocation )
        {
            if ( $userLocation->parentLocationId == $groupMainLocation->id )
            {
                $locationService->deleteLocation( $userLocation );
                return;
            }
        }

        throw new InvalidArgumentException( "userGroup", "user is not in the given user group" );
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
        $locationService = $this->repository->getLocationService();

        $userLocations = $locationService->loadLocations(
            $user->getVersionInfo()->getContentInfo()
        );

        $parentLocationIds = array();
        foreach ( $userLocations as $userLocation )
        {
            if ( $userLocation->parentLocationId !== null )
                $parentLocationIds[] = $userLocation->parentLocationId;
        }

        $searchQuery = new Query();

        $searchQuery->offset = 0;
        $searchQuery->limit = null;

        $searchQuery->criterion = new CriterionLogicalAnd(
            array(
                new CriterionContentTypeId( $this->settings['userGroupClassID'] ),
                new CriterionLocationId( $parentLocationIds ),
                new CriterionStatus( CriterionStatus::STATUS_PUBLISHED )
            )
        );

        $searchResult = $this->repository->getContentService()->findContent( $searchQuery, array() );

        $userGroups = array();
        foreach ( $searchResult->items as $resultItem )
        {
            $userGroups = $this->buildDomainUserGroupObject( $resultItem );
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
    public function loadUsersOfUserGroup( APIUserGroup $userGroup, $offset = 0, $limit = -1 )
    {
        $mainGroupLocation = $this->repository->getLocationService()->loadMainLocation(
            $userGroup->getVersionInfo()->getContentInfo()
        );

        if ( $mainGroupLocation === null )
            return array();

        $searchQuery = new Query();

        $searchQuery->criterion = new CriterionLogicalAnd(
            array(
                new CriterionContentTypeId( $this->settings['userClassID'] ),
                new CriterionParentLocationId( $mainGroupLocation->id ),
                new CriterionStatus( CriterionStatus::STATUS_PUBLISHED )
            )
        );

        $searchQuery->offset = $offset > 0 ? (int) $offset : 0;
        $searchQuery->limit = $limit >= 1 ? (int) $limit : null;

        $searchQuery->sortClauses = array(
            $this->getSortClauseBySortField( $mainGroupLocation->sortField, $mainGroupLocation->sortOrder )
        );

        $searchResult = $this->repository->getContentService()->findContent( $searchQuery, array() );

        $users = array();
        foreach ( $searchResult->items as $resultItem )
        {
            /** @var $resultItem \eZ\Publish\API\Repository\Values\Content\Content */
            $spiUser = $this->persistenceHandler->userHandler()->load( $resultItem->getVersionInfo()->getContentInfo()->contentId );

            $users[] = $this->buildDomainUserObject( $spiUser, $resultItem );
        }

        return $users;
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
        if ( $contentType === null )
        {
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $this->settings['userClassID']
            );
        }

        return new UserCreateStruct(
            array(
                'contentType'      => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'login'            => $login,
                'email'            => $email,
                'password'         => $password,
                'enabled'          => true,
                'fields'           => array(),
            )
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
        if ( $contentType === null )
        {
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $this->settings['userGroupClassID']
            );
        }

        return new UserGroupCreateStruct(
            array(
                'contentType'      => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'fields'           => array(),
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
     * Builds the domain UserGroup object from provided Content object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    protected function buildDomainUserGroupObject( Content $content )
    {
        $contentInfo = $content->getVersionInfo()->getContentInfo();
        $mainLocation = $this->repository->getLocationService()->loadMainLocation( $contentInfo );

        $subGroupCount = 0;
        if ( $mainLocation !== null )
        {
            $subGroups = $this->searchSubGroups( $mainLocation->id, null, Location::SORT_ORDER_ASC, 0, 0 );
            $subGroupCount = $subGroups->count;
        }

        return new UserGroup(
            array(
                'versionInfo'   => $content->getVersionInfo(),
                'fields'        => $content->getFields(),
                'relations'     => $content->getRelations(),
                'parentId'      => $mainLocation ? $mainLocation->parentLocationId : null,
                'subGroupCount' => $subGroupCount
            )
        );
    }

    /**
     * Builds the domain user object from provided persistence user object
     *
     * @param \eZ\Publish\SPI\Persistence\User $spiUser
     * @param \eZ\Publish\API\Repository\Values\Content\Content|null $content
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function buildDomainUserObject( SPIUser $spiUser, Content $content = null )
    {
        if ( $content === null )
            $content = $this->repository->getContentService()->loadContent( $spiUser->id );

        return new User(
            array(
                'versionInfo'   => $content->getVersionInfo(),
                'fields'        => $content->getFields(),
                'relations'     => $content->getRelations(),
                'login'         => $spiUser->login,
                'email'         => $spiUser->email,
                'passwordHash'  => $spiUser->passwordHash,
                'hashAlgorithm' => $spiUser->hashAlgorithm,
                'isEnabled'     => $spiUser->isEnabled,
                'maxLogin'      => $spiUser->maxLogin,
            )
        );
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

    /**
     * Instantiates a correct sort clause object based on provided location sort field and sort order
     *
     * @param int $sortField
     * @param int $sortOrder
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause
     */
    protected function getSortClauseBySortField( $sortField, $sortOrder = Location::SORT_ORDER_ASC )
    {
        $sortOrder = $sortOrder == Location::SORT_ORDER_DESC ? Query::SORT_DESC : Query::SORT_ASC;
        switch ( $sortField )
        {
            case Location::SORT_FIELD_PATH:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPath( $sortOrder );

            case Location::SORT_FIELD_PUBLISHED:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\DatePublished( $sortOrder );

            case Location::SORT_FIELD_MODIFIED:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\DateModified( $sortOrder );

            case Location::SORT_FIELD_SECTION:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\SectionIdentifier( $sortOrder );

            case Location::SORT_FIELD_DEPTH:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationDepth( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_IDENTIFIER:

            //@todo: enable
            // case APILocation::SORT_FIELD_CLASS_NAME:

            case Location::SORT_FIELD_PRIORITY:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPriority( $sortOrder );

            case Location::SORT_FIELD_NAME:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName( $sortOrder );

            //@todo: enable
            // case APILocation::SORT_FIELD_MODIFIED_SUBNODE:

            //@todo: enable
            // case APILocation::SORT_FIELD_NODE_ID:

            //@todo: enable
            // case APILocation::SORT_FIELD_CONTENTOBJECT_ID:

            default:
                return new \eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPath( $sortOrder );
        }
    }
}
