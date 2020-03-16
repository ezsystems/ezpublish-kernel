<?php

/**
 * File containing the eZ\Publish\Core\Repository\UserService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId as CriterionContentTypeId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId as CriterionLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\User\PasswordInfo;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\Core\Repository\Validator\UserPasswordValidator;
use eZ\Publish\Core\Repository\User\PasswordHashServiceInterface;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct as APIUserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\User\UserGroup as APIUserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct as APIUserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\User\Value as UserValue;
use eZ\Publish\Core\FieldType\User\Type as UserType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\User as SPIUser;
use eZ\Publish\SPI\Persistence\User\Handler;
use eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct as SPIUserTokenUpdateStruct;
use Psr\Log\LoggerInterface;

/**
 * This service provides methods for managing users and user groups.
 *
 * @example Examples/user.php
 */
class UserService implements UserServiceInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\SPI\Persistence\User\Handler */
    protected $userHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler */
    private $locationHandler;

    /** @var array */
    protected $settings;

    /** @var \Psr\Log\LoggerInterface|null */
    protected $logger;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\Core\Repository\User\PasswordHashServiceInterface */
    private $passwordHashService;

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\User\Handler $userHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        PermissionResolver $permissionResolver,
        Handler $userHandler,
        LocationHandler $locationHandler,
        PasswordHashServiceInterface $passwordHashGenerator,
        array $settings = []
    ) {
        $this->repository = $repository;
        $this->permissionResolver = $permissionResolver;
        $this->userHandler = $userHandler;
        $this->locationHandler = $locationHandler;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + [
            'defaultUserPlacement' => 12,
            'userClassID' => 4, // @todo Rename this settings to swap out "Class" for "Type"
            'userGroupClassID' => 3,
            'hashType' => $passwordHashGenerator->getDefaultHashType(),
            'siteName' => 'ez.no',
        ];
        $this->passwordHashService = $passwordHashGenerator;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or set to an empty value
     */
    public function createUserGroup(APIUserGroupCreateStruct $userGroupCreateStruct, APIUserGroup $parentGroup)
    {
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();
        $contentTypeService = $this->repository->getContentTypeService();

        if ($userGroupCreateStruct->contentType === null) {
            $userGroupContentType = $contentTypeService->loadContentType($this->settings['userGroupClassID']);
            $userGroupCreateStruct->contentType = $userGroupContentType;
        }

        $loadedParentGroup = $this->loadUserGroup($parentGroup->id);

        if ($loadedParentGroup->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            throw new InvalidArgumentException('parentGroup', 'parent User Group has no main Location');
        }

        $locationCreateStruct = $locationService->newLocationCreateStruct(
            $loadedParentGroup->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $this->repository->beginTransaction();
        try {
            $contentDraft = $contentService->createContent($userGroupCreateStruct, [$locationCreateStruct]);
            $publishedContent = $contentService->publishVersion($contentDraft->getVersionInfo());
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainUserGroupObject($publishedContent);
    }

    /**
     * Loads a user group for the given id.
     *
     * @param mixed $id
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the user group with the given id was not found
     */
    public function loadUserGroup($id, array $prioritizedLanguages = [])
    {
        $content = $this->repository->getContentService()->loadContent($id, $prioritizedLanguages);

        return $this->buildDomainUserGroupObject($content);
    }

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
    public function loadSubUserGroups(APIUserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        $locationService = $this->repository->getLocationService();

        $loadedUserGroup = $this->loadUserGroup($userGroup->id);
        if (!$this->permissionResolver->canUser('content', 'read', $loadedUserGroup)) {
            throw new UnauthorizedException('content', 'read');
        }

        if ($loadedUserGroup->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            return [];
        }

        $mainGroupLocation = $locationService->loadLocation(
            $loadedUserGroup->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $searchResult = $this->searchSubGroups($mainGroupLocation, $offset, $limit);
        if ($searchResult->totalCount == 0) {
            return [];
        }

        $subUserGroups = [];
        foreach ($searchResult->searchHits as $searchHit) {
            $subUserGroups[] = $this->buildDomainUserGroupObject(
                $this->repository->getContentService()->internalLoadContentById(
                    $searchHit->valueObject->contentInfo->id,
                    $prioritizedLanguages
                )
            );
        }

        return $subUserGroups;
    }

    /**
     * Returns (searches) subgroups of a user group described by its main location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    protected function searchSubGroups(Location $location, $offset = 0, $limit = 25)
    {
        $searchQuery = new LocationQuery();

        $searchQuery->offset = $offset;
        $searchQuery->limit = $limit;

        $searchQuery->filter = new CriterionLogicalAnd([
            new CriterionContentTypeId($this->settings['userGroupClassID']),
            new CriterionParentLocationId($location->id),
        ]);

        $searchQuery->sortClauses = $location->getSortClauses();

        return $this->repository->getSearchService()->findLocations($searchQuery, [], false);
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
    public function deleteUserGroup(APIUserGroup $userGroup)
    {
        $loadedUserGroup = $this->loadUserGroup($userGroup->id);

        $this->repository->beginTransaction();
        try {
            //@todo: what happens to sub user groups and users below sub user groups
            $affectedLocationIds = $this->repository->getContentService()->deleteContent($loadedUserGroup->getVersionInfo()->getContentInfo());
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $affectedLocationIds;
    }

    /**
     * Moves the user group to another parent.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     */
    public function moveUserGroup(APIUserGroup $userGroup, APIUserGroup $newParent)
    {
        $loadedUserGroup = $this->loadUserGroup($userGroup->id);
        $loadedNewParent = $this->loadUserGroup($newParent->id);

        $locationService = $this->repository->getLocationService();

        if ($loadedUserGroup->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            throw new BadStateException('userGroup', 'existing User Group is not stored and/or does not have any Location yet');
        }

        if ($loadedNewParent->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            throw new BadStateException('newParent', 'new User Group is not stored and/or does not have any Location yet');
        }

        $userGroupMainLocation = $locationService->loadLocation(
            $loadedUserGroup->getVersionInfo()->getContentInfo()->mainLocationId
        );
        $newParentMainLocation = $locationService->loadLocation(
            $loadedNewParent->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $this->repository->beginTransaction();
        try {
            $locationService->moveSubtree($userGroupMainLocation, $newParentMainLocation);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userGroupUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     */
    public function updateUserGroup(APIUserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        if ($userGroupUpdateStruct->contentUpdateStruct === null &&
            $userGroupUpdateStruct->contentMetadataUpdateStruct === null) {
            // both update structs are empty, nothing to do
            return $userGroup;
        }

        $contentService = $this->repository->getContentService();

        $loadedUserGroup = $this->loadUserGroup($userGroup->id);

        $this->repository->beginTransaction();
        try {
            $publishedContent = $loadedUserGroup;
            if ($userGroupUpdateStruct->contentUpdateStruct !== null) {
                $contentDraft = $contentService->createContentDraft($loadedUserGroup->getVersionInfo()->getContentInfo());

                $contentDraft = $contentService->updateContent(
                    $contentDraft->getVersionInfo(),
                    $userGroupUpdateStruct->contentUpdateStruct
                );

                $publishedContent = $contentService->publishVersion($contentDraft->getVersionInfo());
            }

            if ($userGroupUpdateStruct->contentMetadataUpdateStruct !== null) {
                $publishedContent = $contentService->updateContentMetadata(
                    $publishedContent->getVersionInfo()->getContentInfo(),
                    $userGroupUpdateStruct->contentMetadataUpdateStruct
                );
            }

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainUserGroupObject($publishedContent);
    }

    /**
     * Create a new user. The created user is published by this method.
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
    public function createUser(APIUserCreateStruct $userCreateStruct, array $parentGroups)
    {
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $locationCreateStructs = [];
        foreach ($parentGroups as $parentGroup) {
            $parentGroup = $this->loadUserGroup($parentGroup->id);
            if ($parentGroup->getVersionInfo()->getContentInfo()->mainLocationId !== null) {
                $locationCreateStructs[] = $locationService->newLocationCreateStruct(
                    $parentGroup->getVersionInfo()->getContentInfo()->mainLocationId
                );
            }
        }

        // Search for the first ezuser field type in content type
        $userFieldDefinition = $this->getUserFieldDefinition($userCreateStruct->contentType);
        if ($userFieldDefinition === null) {
            throw new ContentValidationException('the provided Content Type does not contain the ezuser Field Type');
        }

        $this->repository->beginTransaction();
        try {
            $contentDraft = $contentService->createContent($userCreateStruct, $locationCreateStructs);
            // There is no need to create user separately, just load it from SPI
            $spiUser = $this->userHandler->load($contentDraft->id);
            $publishedContent = $contentService->publishVersion($contentDraft->getVersionInfo());

            // User\Handler::create call is currently used to clear cache only
            $this->userHandler->create(
                new SPIUser(
                    [
                        'id' => $spiUser->id,
                        'login' => $spiUser->login,
                        'email' => $spiUser->email,
                    ]
                )
            );

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainUserObject($spiUser, $publishedContent);
    }

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
    public function loadUser($userId, array $prioritizedLanguages = [])
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        $content = $this->repository->getContentService()->internalLoadContentById($userId, $prioritizedLanguages);
        // Get spiUser value from Field Value
        foreach ($content->getFields() as $field) {
            if (!$field->value instanceof UserValue) {
                continue;
            }

            /** @var \eZ\Publish\Core\FieldType\User\Value $value */
            $value = $field->value;
            $spiUser = new SPIUser();
            $spiUser->id = $value->contentId;
            $spiUser->login = $value->login;
            $spiUser->email = $value->email;
            $spiUser->hashAlgorithm = $value->passwordHashType;
            $spiUser->passwordHash = $value->passwordHash;
            $spiUser->passwordUpdatedAt = $value->passwordUpdatedAt ? $value->passwordUpdatedAt->getTimestamp() : null;
            $spiUser->isEnabled = $value->enabled;
            $spiUser->maxLogin = $value->maxLogin;
            break;
        }

        // If for some reason not found, load it
        if (!isset($spiUser)) {
            $spiUser = $this->userHandler->load($userId);
        }

        return $this->buildDomainUserObject($spiUser, $content);
    }

    /**
     * Loads a user for the given login and password.
     *
     * If the password hash type differs from that configured for the service, it will be updated to the configured one.
     *
     * {@inheritdoc}
     *
     * @param string $login
     * @param string $password the plain password
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if credentials are invalid
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given credentials was not found
     */
    public function loadUserByCredentials($login, $password, array $prioritizedLanguages = [])
    {
        if (!is_string($login) || empty($login)) {
            throw new InvalidArgumentValue('login', $login);
        }

        if (!is_string($password)) {
            throw new InvalidArgumentValue('password', $password);
        }

        $spiUser = $this->userHandler->loadByLogin($login);
        if (!$this->comparePasswordHashForSPIUser($login, $password, $spiUser)) {
            throw new NotFoundException('user', $login);
        }

        // Don't catch BadStateException, on purpose, to avoid broken hashes.
        $this->updatePasswordHash($login, $password, $spiUser);

        return $this->buildDomainUserObject($spiUser, null, $prioritizedLanguages);
    }

    /**
     * Update password hash to the type configured for the service, if they differ.
     *
     * @param string $login User login
     * @param string $password User password
     * @param \eZ\Publish\SPI\Persistence\User $spiUser
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException if the password is not correctly saved, in which case the update is reverted
     */
    private function updatePasswordHash($login, $password, SPIUser $spiUser)
    {
        $hashType = $this->passwordHashService->getDefaultHashType();
        if ($spiUser->hashAlgorithm === $hashType) {
            return;
        }

        $spiUser->passwordHash = $this->passwordHashService->createPasswordHash($password, $hashType);
        $spiUser->hashAlgorithm = $hashType;

        $this->repository->beginTransaction();
        $this->userHandler->update($spiUser);
        $reloadedSpiUser = $this->userHandler->load($spiUser->id);

        if ($reloadedSpiUser->passwordHash === $spiUser->passwordHash) {
            $this->repository->commit();
        } else {
            // Password hash was not correctly saved, possible cause: EZP-28692
            $this->repository->rollback();
            if (isset($this->logger)) {
                $this->logger->critical('Password hash could not be updated. Please verify that your database schema is up to date.');
            }

            throw new BadStateException(
                'user',
                'Could not save updated password hash, reverting to previous hash. Please verify that your database schema is up to date.'
            );
        }
    }

    /**
     * Loads a user for the given login.
     *
     * {@inheritdoc}
     *
     * @param string $login
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given credentials was not found
     */
    public function loadUserByLogin($login, array $prioritizedLanguages = [])
    {
        if (!is_string($login) || empty($login)) {
            throw new InvalidArgumentValue('login', $login);
        }

        $spiUser = $this->userHandler->loadByLogin($login);

        return $this->buildDomainUserObject($spiUser, null, $prioritizedLanguages);
    }

    /**
     * Loads a user for the given email.
     *
     * {@inheritdoc}
     *
     * @param string $email
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User[]
     */
    public function loadUsersByEmail($email, array $prioritizedLanguages = [])
    {
        if (!is_string($email) || empty($email)) {
            throw new InvalidArgumentValue('email', $email);
        }

        $users = [];
        foreach ($this->userHandler->loadByEmail($email) as $spiUser) {
            $users[] = $this->buildDomainUserObject($spiUser, null, $prioritizedLanguages);
        }

        return $users;
    }

    /**
     * Loads a user for the given token.
     *
     * {@inheritdoc}
     *
     * @param string $hash
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     */
    public function loadUserByToken($hash, array $prioritizedLanguages = [])
    {
        if (!is_string($hash) || empty($hash)) {
            throw new InvalidArgumentValue('hash', $hash);
        }

        $spiUser = $this->userHandler->loadUserByToken($hash);

        return $this->buildDomainUserObject($spiUser, null, $prioritizedLanguages);
    }

    /**
     * This method deletes a user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete the user
     */
    public function deleteUser(APIUser $user)
    {
        $loadedUser = $this->loadUser($user->id);

        $this->repository->beginTransaction();
        try {
            $affectedLocationIds = $this->repository->getContentService()->deleteContent($loadedUser->getVersionInfo()->getContentInfo());

            // User\Handler::delete call is currently used to clear cache only
            $this->userHandler->delete($loadedUser->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $affectedLocationIds;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUser(APIUser $user, UserUpdateStruct $userUpdateStruct)
    {
        $loadedUser = $this->loadUser($user->id);

        $contentService = $this->repository->getContentService();

        $canEditContent = $this->permissionResolver->canUser('content', 'edit', $loadedUser);

        if (!$canEditContent && $this->isUserProfileUpdateRequested($userUpdateStruct)) {
            throw new UnauthorizedException('content', 'edit');
        }

        $userFieldDefinition = null;
        foreach ($loadedUser->getContentType()->fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier === 'ezuser') {
                $userFieldDefinition = $fieldDefinition;
                break;
            }
        }

        if ($userFieldDefinition === null) {
            throw new ContentValidationException('The provided Content Type does not contain the ezuser Field Type');
        }

        $userUpdateStruct->contentUpdateStruct = $userUpdateStruct->contentUpdateStruct ?? $contentService->newContentUpdateStruct();

        $providedUserUpdateDataInField = false;
        foreach ($userUpdateStruct->contentUpdateStruct->fields as $field) {
            if ($field->value instanceof UserValue) {
                $providedUserUpdateDataInField = true;
                break;
            }
        }

        if (!$providedUserUpdateDataInField) {
            $userUpdateStruct->contentUpdateStruct->setField(
                $userFieldDefinition->identifier,
                new UserValue([
                    'contentId' => $loadedUser->id,
                    'hasStoredLogin' => true,
                    'login' => $loadedUser->login,
                    'email' => $userUpdateStruct->email ?? $loadedUser->email,
                    'plainPassword' => $userUpdateStruct->password,
                    'enabled' => $userUpdateStruct->enabled ?? $loadedUser->enabled,
                    'maxLogin' => $userUpdateStruct->maxLogin ?? $loadedUser->maxLogin,
                    'passwordHashType' => $user->hashAlgorithm,
                    'passwordHash' => $user->passwordHash,
                ])
            );
        }

        if (!empty($userUpdateStruct->password) &&
            !$canEditContent &&
            !$this->permissionResolver->canUser('user', 'password', $loadedUser)
        ) {
            throw new UnauthorizedException('user', 'password');
        }

        $this->repository->beginTransaction();
        try {
            $publishedContent = $loadedUser;
            if ($userUpdateStruct->contentUpdateStruct !== null) {
                $contentDraft = $contentService->createContentDraft($loadedUser->getVersionInfo()->getContentInfo());
                $contentDraft = $contentService->updateContent(
                    $contentDraft->getVersionInfo(),
                    $userUpdateStruct->contentUpdateStruct
                );
                $publishedContent = $contentService->publishVersion($contentDraft->getVersionInfo());
            }

            if ($userUpdateStruct->contentMetadataUpdateStruct !== null) {
                $contentService->updateContentMetadata(
                    $publishedContent->getVersionInfo()->getContentInfo(),
                    $userUpdateStruct->contentMetadataUpdateStruct
                );
            }

            // User\Handler::update call is currently used to clear cache only
            $this->userHandler->update(
                new SPIUser(
                    [
                        'id' => $loadedUser->id,
                        'login' => $loadedUser->login,
                        'email' => $userUpdateStruct->email ?: $loadedUser->email,
                    ]
                )
            );

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadUser($loadedUser->id);
    }

    /**
     * Update the user token information specified by the user token struct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct $userTokenUpdateStruct
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \RuntimeException
     * @throws \Exception
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUserToken(APIUser $user, UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        $loadedUser = $this->loadUser($user->id);

        if ($userTokenUpdateStruct->hashKey !== null && (!is_string($userTokenUpdateStruct->hashKey) || empty($userTokenUpdateStruct->hashKey))) {
            throw new InvalidArgumentValue('hashKey', $userTokenUpdateStruct->hashKey, 'UserTokenUpdateStruct');
        }

        if ($userTokenUpdateStruct->time === null) {
            throw new InvalidArgumentValue('time', $userTokenUpdateStruct->time, 'UserTokenUpdateStruct');
        }

        $this->repository->beginTransaction();
        try {
            $this->userHandler->updateUserToken(
                new SPIUserTokenUpdateStruct(
                    [
                        'userId' => $loadedUser->id,
                        'hashKey' => $userTokenUpdateStruct->hashKey,
                        'time' => $userTokenUpdateStruct->time->getTimestamp(),
                    ]
                )
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadUser($loadedUser->id);
    }

    /**
     * Expires user token with user hash.
     *
     * @param string $hash
     */
    public function expireUserToken($hash)
    {
        $this->repository->beginTransaction();
        try {
            $this->userHandler->expireUserToken($hash);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
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
    public function assignUserToUserGroup(APIUser $user, APIUserGroup $userGroup)
    {
        $loadedUser = $this->loadUser($user->id);
        $loadedGroup = $this->loadUserGroup($userGroup->id);
        $locationService = $this->repository->getLocationService();

        $existingGroupIds = [];
        $userLocations = $locationService->loadLocations($loadedUser->getVersionInfo()->getContentInfo());
        foreach ($userLocations as $userLocation) {
            $existingGroupIds[] = $userLocation->parentLocationId;
        }

        if ($loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            throw new BadStateException('userGroup', 'User Group has no main Location or no Locations');
        }

        if (in_array($loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId, $existingGroupIds)) {
            // user is already assigned to the user group
            throw new InvalidArgumentException('user', 'User is already in the given User Group');
        }

        $locationCreateStruct = $locationService->newLocationCreateStruct(
            $loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $this->repository->beginTransaction();
        try {
            $locationService->createLocation(
                $loadedUser->getVersionInfo()->getContentInfo(),
                $locationCreateStruct
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

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
    public function unAssignUserFromUserGroup(APIUser $user, APIUserGroup $userGroup)
    {
        $loadedUser = $this->loadUser($user->id);
        $loadedGroup = $this->loadUserGroup($userGroup->id);
        $locationService = $this->repository->getLocationService();

        $userLocations = $locationService->loadLocations($loadedUser->getVersionInfo()->getContentInfo());
        if (empty($userLocations)) {
            throw new BadStateException('user', 'User has no Locations, cannot unassign from group');
        }

        if ($loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            throw new BadStateException('userGroup', 'User Group has no main Location or no Locations, cannot unassign');
        }

        foreach ($userLocations as $userLocation) {
            if ($userLocation->parentLocationId == $loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId) {
                // Throw this specific BadState when we know argument is valid
                if (count($userLocations) === 1) {
                    throw new BadStateException('user', 'User only has one User Group, cannot unassign from last group');
                }

                $this->repository->beginTransaction();
                try {
                    $locationService->deleteLocation($userLocation);
                    $this->repository->commit();

                    return;
                } catch (Exception $e) {
                    $this->repository->rollback();
                    throw $e;
                }
            }
        }

        throw new InvalidArgumentException('userGroup', 'User is not in the given User Group');
    }

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
    public function loadUserGroupsOfUser(APIUser $user, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        $locationService = $this->repository->getLocationService();

        if (!$this->repository->getPermissionResolver()->canUser('content', 'read', $user)) {
            throw new UnauthorizedException('content', 'read');
        }

        $userLocations = $locationService->loadLocations(
            $user->getVersionInfo()->getContentInfo()
        );

        $parentLocationIds = [];
        foreach ($userLocations as $userLocation) {
            if ($userLocation->parentLocationId !== null) {
                $parentLocationIds[] = $userLocation->parentLocationId;
            }
        }

        $searchQuery = new LocationQuery();

        $searchQuery->offset = $offset;
        $searchQuery->limit = $limit;
        $searchQuery->performCount = false;

        $searchQuery->filter = new CriterionLogicalAnd(
            [
                new CriterionContentTypeId($this->settings['userGroupClassID']),
                new CriterionLocationId($parentLocationIds),
            ]
        );

        $searchResult = $this->repository->getSearchService()->findLocations($searchQuery);

        $userGroups = [];
        foreach ($searchResult->searchHits as $resultItem) {
            $userGroups[] = $this->buildDomainUserGroupObject(
                $this->repository->getContentService()->internalLoadContentById(
                    $resultItem->valueObject->contentInfo->id,
                    $prioritizedLanguages
                )
            );
        }

        return $userGroups;
    }

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
    public function loadUsersOfUserGroup(
        APIUserGroup $userGroup,
        $offset = 0,
        $limit = 25,
        array $prioritizedLanguages = []
    ) {
        $loadedUserGroup = $this->loadUserGroup($userGroup->id);

        if ($loadedUserGroup->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            return [];
        }

        $mainGroupLocation = $this->repository->getLocationService()->loadLocation(
            $loadedUserGroup->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $searchQuery = new LocationQuery();

        $searchQuery->filter = new CriterionLogicalAnd(
            [
                new CriterionContentTypeId($this->settings['userClassID']),
                new CriterionParentLocationId($mainGroupLocation->id),
            ]
        );

        $searchQuery->offset = $offset;
        $searchQuery->limit = $limit;
        $searchQuery->performCount = false;
        $searchQuery->sortClauses = $mainGroupLocation->getSortClauses();

        $searchResult = $this->repository->getSearchService()->findLocations($searchQuery);

        $users = [];
        foreach ($searchResult->searchHits as $resultItem) {
            $users[] = $this->buildDomainUserObject(
                $this->userHandler->load($resultItem->valueObject->contentInfo->id),
                $this->repository->getContentService()->internalLoadContentById(
                    $resultItem->valueObject->contentInfo->id,
                    $prioritizedLanguages
                )
            );
        }

        return $users;
    }

    /**
     * {@inheritdoc}
     */
    public function isUser(APIContent $content): bool
    {
        // First check against config for fast check
        if ($this->settings['userClassID'] == $content->getVersionInfo()->getContentInfo()->contentTypeId) {
            return true;
        }

        // For users we ultimately need to look for ezuser type as content type id could be several for users.
        // And config might be different from one SA to the next, which we don't care about here.
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
    public function isUserGroup(APIContent $content): bool
    {
        return $this->settings['userGroupClassID'] == $content->getVersionInfo()->getContentInfo()->contentTypeId;
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
        if ($contentType === null) {
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $this->settings['userClassID']
            );
        }

        $fieldDefIdentifier = '';
        foreach ($contentType->fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier === 'ezuser') {
                $fieldDefIdentifier = $fieldDefinition->identifier;
                break;
            }
        }

        return new UserCreateStruct(
            [
                'contentType' => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'login' => $login,
                'email' => $email,
                'password' => $password,
                'enabled' => true,
                'fields' => [
                    new Field([
                        'fieldDefIdentifier' => $fieldDefIdentifier,
                        'languageCode' => $mainLanguageCode,
                        'fieldTypeIdentifier' => 'ezuser',
                        'value' => new UserValue([
                            'login' => $login,
                            'email' => $email,
                            'plainPassword' => $password,
                            'enabled' => true,
                            'passwordUpdatedAt' => new DateTime(),
                        ]),
                    ]),
                ],
            ]
        );
    }

    /**
     * Instantiate a user group create class.
     *
     * @param string $mainLanguageCode The main language for the underlying content object
     * @param null|\eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct
     */
    public function newUserGroupCreateStruct($mainLanguageCode, $contentType = null)
    {
        if ($contentType === null) {
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $this->settings['userGroupClassID']
            );
        }

        return new UserGroupCreateStruct(
            [
                'contentType' => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'fields' => [],
            ]
        );
    }

    /**
     * Instantiate a new user update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserUpdateStruct
     */
    public function newUserUpdateStruct()
    {
        return new UserUpdateStruct();
    }

    /**
     * Instantiate a new user group update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct
     */
    public function newUserGroupUpdateStruct()
    {
        return new UserGroupUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword(string $password, PasswordValidationContext $context = null): array
    {
        $errors = [];

        if ($context === null) {
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $this->settings['userClassID']
            );

            $context = new PasswordValidationContext([
                'contentType' => $contentType,
            ]);
        }

        // Search for the first ezuser field type in content type
        $userFieldDefinition = $this->getUserFieldDefinition($context->contentType);
        if ($userFieldDefinition === null) {
            throw new ContentValidationException('The provided Content Type does not contain the ezuser Field Type');
        }

        $configuration = $userFieldDefinition->getValidatorConfiguration();
        if (isset($configuration['PasswordValueValidator'])) {
            $errors = (new UserPasswordValidator($configuration['PasswordValueValidator']))->validate($password);
        }

        if ($context->user !== null) {
            $isPasswordTTLEnabled = $this->getPasswordInfo($context->user)->hasExpirationDate();
            $isNewPasswordRequired = $configuration['PasswordValueValidator']['requireNewPassword'] ?? false;

            if (($isPasswordTTLEnabled || $isNewPasswordRequired) &&
                $this->comparePasswordHashForAPIUser($context->user->login, $password, $context->user)
            ) {
                $errors[] = new ValidationError('New password cannot be the same as old password', null, [], 'password');
            }
        }

        return $errors;
    }

    /**
     * Builds the domain UserGroup object from provided Content object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    protected function buildDomainUserGroupObject(APIContent $content)
    {
        $locationService = $this->repository->getLocationService();

        if ($content->getVersionInfo()->getContentInfo()->mainLocationId !== null) {
            $mainLocation = $locationService->loadLocation(
                $content->getVersionInfo()->getContentInfo()->mainLocationId
            );
            $parentLocation = $this->locationHandler->load($mainLocation->parentLocationId);
        }

        return new UserGroup(
            [
                'content' => $content,
                'parentId' => $parentLocation->contentId ?? null,
            ]
        );
    }

    /**
     * Builds the domain user object from provided persistence user object.
     *
     * @param \eZ\Publish\SPI\Persistence\User $spiUser
     * @param \eZ\Publish\API\Repository\Values\Content\Content|null $content
     * @param string[] $prioritizedLanguages Used as prioritized language code on translated properties of returned object.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function buildDomainUserObject(
        SPIUser $spiUser,
        APIContent $content = null,
        array $prioritizedLanguages = []
    ) {
        if ($content === null) {
            $content = $this->repository->getContentService()->internalLoadContentById(
                $spiUser->id,
                $prioritizedLanguages
            );
        }

        return new User(
            [
                'content' => $content,
                'login' => $spiUser->login,
                'email' => $spiUser->email,
                'passwordHash' => $spiUser->passwordHash,
                'passwordUpdatedAt' => $this->getDateTime($spiUser->passwordUpdatedAt),
                'hashAlgorithm' => (int)$spiUser->hashAlgorithm,
                'enabled' => $spiUser->isEnabled,
                'maxLogin' => (int)$spiUser->maxLogin,
            ]
        );
    }

    public function getPasswordInfo(APIUser $user): PasswordInfo
    {
        $passwordUpdatedAt = $user->passwordUpdatedAt;
        if ($passwordUpdatedAt === null) {
            return new PasswordInfo();
        }

        $definition = $this->getUserFieldDefinition($user->getContentType());
        if ($definition === null) {
            return new PasswordInfo();
        }

        $expirationDate = null;
        $expirationWarningDate = null;

        $passwordTTL = (int)$definition->fieldSettings[UserType::PASSWORD_TTL_SETTING];
        if ($passwordTTL > 0) {
            if ($passwordUpdatedAt instanceof DateTime) {
                $passwordUpdatedAt = DateTimeImmutable::createFromMutable($passwordUpdatedAt);
            }

            $expirationDate = $passwordUpdatedAt->add(new DateInterval(sprintf('P%dD', $passwordTTL)));

            $passwordTTLWarning = (int)$definition->fieldSettings[UserType::PASSWORD_TTL_WARNING_SETTING];
            if ($passwordTTLWarning > 0) {
                $expirationWarningDate = $expirationDate->sub(new DateInterval(sprintf('P%dD', $passwordTTLWarning)));
            }
        }

        return new PasswordInfo($expirationDate, $expirationWarningDate);
    }

    private function getUserFieldDefinition(ContentType $contentType): ?FieldDefinition
    {
        return $contentType->getFirstFieldDefinitionOfType('ezuser');
    }

    /**
     * Verifies if the provided login and password are valid for eZ\Publish\SPI\Persistence\User.
     *
     * @param string $login User login
     * @param string $password User password
     * @param \eZ\Publish\SPI\Persistence\User $spiUser Loaded user handler
     *
     * @return bool return true if the login and password are sucessfully validated and false, if not.
     */
    protected function comparePasswordHashForSPIUser(string $login, string $password, SPIUser $spiUser): bool
    {
        return $this->comparePasswordHashes($login, $password, $spiUser->passwordHash, $spiUser->hashAlgorithm);
    }

    /**
     * Verifies if the provided login and password are valid for eZ\Publish\API\Repository\Values\User\User.
     *
     * @param string $login User login
     * @param string $password User password
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser Loaded user
     *
     * @return bool return true if the login and password are sucessfully validated and false, if not.
     */
    protected function comparePasswordHashForAPIUser(string $login, string $password, APIUser $apiUser): bool
    {
        return $this->comparePasswordHashes($login, $password, $apiUser->passwordHash, $apiUser->hashAlgorithm);
    }

    /**
     * Verifies if the provided login and password are valid against given password hash and hash type.
     *
     * @param string $login User login
     * @param string $plainPassword User password
     * @param string $passwordHash User password hash
     * @param int $hashAlgorithm Hash type
     *
     * @return bool return true if the login and password are sucessfully validated and false, if not.
     */
    private function comparePasswordHashes(
        string $login,
        string $plainPassword,
        string $passwordHash,
        int $hashAlgorithm
    ): bool {
        return $this->passwordHashService->isValidPassword($plainPassword, $passwordHash, $hashAlgorithm);
    }

    /**
     * Return true if any of the UserUpdateStruct properties refers to User Profile (Content) update.
     *
     * @param UserUpdateStruct $userUpdateStruct
     *
     * @return bool
     */
    private function isUserProfileUpdateRequested(UserUpdateStruct $userUpdateStruct)
    {
        return
            !empty($userUpdateStruct->contentUpdateStruct) ||
            !empty($userUpdateStruct->contentMetadataUpdateStruct) ||
            !empty($userUpdateStruct->email) ||
            !empty($userUpdateStruct->enabled) ||
            !empty($userUpdateStruct->maxLogin);
    }

    private function getDateTime(?int $timestamp): ?DateTimeInterface
    {
        if ($timestamp !== null) {
            // Instead of using DateTime(ts) we use setTimeStamp() so timezone does not get set to UTC
            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp);

            return DateTimeImmutable::createFromMutable($dateTime);
        }

        return null;
    }
}
