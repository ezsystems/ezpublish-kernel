<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId as CriterionContentTypeId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId as CriterionLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId as CriterionParentLocationId;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\User\PasswordInfo;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct as APIUserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup as APIUserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct as APIUserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Base\Exceptions\UserPasswordValidationException;
use eZ\Publish\Core\FieldType\User\Value as UserValue;
use eZ\Publish\Core\FieldType\User\Type as UserType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Repository\Validator\UserPasswordValidator;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
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
        Handler $userHandler,
        LocationHandler $locationHandler,
        array $settings = []
    ) {
        $this->repository = $repository;
        $this->permissionResolver = $repository->getPermissionResolver();
        $this->userHandler = $userHandler;
        $this->locationHandler = $locationHandler;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + [
            'defaultUserPlacement' => 12,
            'userClassID' => 4, // @todo Rename this settings to swap out "Class" for "Type"
            'userGroupClassID' => 3,
            'hashType' => APIUser::DEFAULT_PASSWORD_HASH,
            'siteName' => 'ez.no',
        ];
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
            throw new InvalidArgumentException('parentGroup', 'parent user group has no main location');
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
        if (!$this->repository->canUser('content', 'read', $loadedUserGroup)) {
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
                $this->repository->getContentService()->internalLoadContent(
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
            throw new BadStateException('userGroup', 'existing user group is not stored and/or does not have any location yet');
        }

        if ($loadedNewParent->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            throw new BadStateException('newParent', 'new user group is not stored and/or does not have any location yet');
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
        if (empty($parentGroups)) {
            throw new InvalidArgumentValue('parentGroups', $parentGroups);
        }

        if (!is_string($userCreateStruct->login) || empty($userCreateStruct->login)) {
            throw new InvalidArgumentValue('login', $userCreateStruct->login, 'UserCreateStruct');
        }

        if (!is_string($userCreateStruct->email) || empty($userCreateStruct->email)) {
            throw new InvalidArgumentValue('email', $userCreateStruct->email, 'UserCreateStruct');
        }

        if (!preg_match('/^.+@.+\..+$/', $userCreateStruct->email)) {
            throw new InvalidArgumentValue('email', $userCreateStruct->email, 'UserCreateStruct');
        }

        if (!is_string($userCreateStruct->password) || empty($userCreateStruct->password)) {
            throw new InvalidArgumentValue('password', $userCreateStruct->password, 'UserCreateStruct');
        }

        if (!is_bool($userCreateStruct->enabled)) {
            throw new InvalidArgumentValue('enabled', $userCreateStruct->enabled, 'UserCreateStruct');
        }

        try {
            $this->userHandler->loadByLogin($userCreateStruct->login);
            throw new InvalidArgumentException('userCreateStruct', 'User with provided login already exists');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();
        $contentTypeService = $this->repository->getContentTypeService();

        if ($userCreateStruct->contentType === null) {
            $userCreateStruct->contentType = $contentTypeService->loadContentType($this->settings['userClassID']);
        }

        $errors = $this->validatePassword($userCreateStruct->password, new PasswordValidationContext([
            'contentType' => $userCreateStruct->contentType,
        ]));
        if (!empty($errors)) {
            throw new UserPasswordValidationException('password', $errors);
        }

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
        $userFieldDefinition = null;
        foreach ($userCreateStruct->contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier == 'ezuser') {
                $userFieldDefinition = $fieldDefinition;
                break;
            }
        }

        if ($userFieldDefinition === null) {
            throw new ContentValidationException('Provided content type does not contain ezuser field type');
        }

        $fixUserFieldType = true;
        foreach ($userCreateStruct->fields as $index => $field) {
            if ($field->fieldDefIdentifier == $userFieldDefinition->identifier) {
                if ($field->value instanceof UserValue) {
                    $userCreateStruct->fields[$index]->value->login = $userCreateStruct->login;
                } else {
                    $userCreateStruct->fields[$index]->value = new UserValue(
                        [
                            'login' => $userCreateStruct->login,
                        ]
                    );
                }

                $fixUserFieldType = false;
            }
        }

        if ($fixUserFieldType) {
            $userCreateStruct->setField(
                $userFieldDefinition->identifier,
                new UserValue(
                    [
                        'login' => $userCreateStruct->login,
                    ]
                )
            );
        }

        $this->repository->beginTransaction();
        try {
            $contentDraft = $contentService->createContent($userCreateStruct, $locationCreateStructs);
            // Create user before publishing, so that external data can be returned
            $spiUser = $this->userHandler->create(
                new SPIUser(
                    [
                        'id' => $contentDraft->id,
                        'login' => $userCreateStruct->login,
                        'email' => $userCreateStruct->email,
                        'passwordHash' => $this->createPasswordHash(
                            $userCreateStruct->login,
                            $userCreateStruct->password,
                            $this->settings['siteName'],
                            $this->settings['hashType']
                        ),
                        'hashAlgorithm' => $this->settings['hashType'],
                        'passwordUpdatedAt' => time(),
                        'isEnabled' => $userCreateStruct->enabled,
                        'maxLogin' => 0,
                    ]
                )
            );
            $publishedContent = $contentService->publishVersion($contentDraft->getVersionInfo());

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
        $content = $this->repository->getContentService()->internalLoadContent($userId, $prioritizedLanguages);
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
     * Loads anonymous user.
     *
     * @deprecated since 5.3, use loadUser( $anonymousUserId ) instead
     *
     * @uses ::loadUser()
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function loadAnonymousUser()
    {
        return $this->loadUser($this->settings['anonymousUserID']);
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
        @trigger_error(
            sprintf('%s method will be removed in eZ Platform 3.0. Use UserService::checkUserCredentials instead.', __METHOD__),
            E_USER_DEPRECATED
        );

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
     * Checks if credentials are valid for provided User.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param string $credentials
     *
     * @return bool
     */
    public function checkUserCredentials(APIUser $user, string $credentials): bool
    {
        return $this->comparePasswordHashForAPIUser($user->login, $credentials, $user);
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
        if ($spiUser->hashAlgorithm === $this->settings['hashType']) {
            return;
        }

        $spiUser->passwordHash = $this->createPasswordHash($login, $password, null, $this->settings['hashType']);
        $spiUser->hashAlgorithm = $this->settings['hashType'];

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

        // We need to determine if we have anything to update.
        // UserUpdateStruct is specific as some of the new content is in
        // content update struct and some of it is in additional fields like
        // email, password and so on
        $doUpdate = false;
        foreach ($userUpdateStruct as $propertyValue) {
            if ($propertyValue !== null) {
                $doUpdate = true;
                break;
            }
        }

        if (!$doUpdate) {
            // Nothing to update, so we just quit
            return $user;
        }

        if ($userUpdateStruct->email !== null) {
            if (!is_string($userUpdateStruct->email) || empty($userUpdateStruct->email)) {
                throw new InvalidArgumentValue('email', $userUpdateStruct->email, 'UserUpdateStruct');
            }

            if (!preg_match('/^.+@.+\..+$/', $userUpdateStruct->email)) {
                throw new InvalidArgumentValue('email', $userUpdateStruct->email, 'UserUpdateStruct');
            }
        }

        if ($userUpdateStruct->enabled !== null && !is_bool($userUpdateStruct->enabled)) {
            throw new InvalidArgumentValue('enabled', $userUpdateStruct->enabled, 'UserUpdateStruct');
        }

        if ($userUpdateStruct->maxLogin !== null && !is_int($userUpdateStruct->maxLogin)) {
            throw new InvalidArgumentValue('maxLogin', $userUpdateStruct->maxLogin, 'UserUpdateStruct');
        }

        if ($userUpdateStruct->password !== null) {
            if (!is_string($userUpdateStruct->password) || empty($userUpdateStruct->password)) {
                throw new InvalidArgumentValue('password', $userUpdateStruct->password, 'UserUpdateStruct');
            }

            $userContentType = $this->repository->getContentTypeService()->loadContentType(
                $user->contentInfo->contentTypeId
            );

            $errors = $this->validatePassword($userUpdateStruct->password, new PasswordValidationContext([
                'contentType' => $userContentType,
                'user' => $user,
            ]));

            if (!empty($errors)) {
                throw new UserPasswordValidationException('password', $errors);
            }
        }

        $contentService = $this->repository->getContentService();

        $canEditContent = $this->permissionResolver->canUser('content', 'edit', $loadedUser);

        if (!$canEditContent && $this->isUserProfileUpdateRequested($userUpdateStruct)) {
            throw new UnauthorizedException('content', 'edit');
        }

        if (!empty($userUpdateStruct->password) &&
            !$canEditContent &&
            !$this->permissionResolver->canUser('user', 'password', $loadedUser, [$loadedUser])
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

            $spiUser = new SPIUser([
                'id' => $loadedUser->id,
                'login' => $loadedUser->login,
                'email' => $userUpdateStruct->email ?: $loadedUser->email,
                'isEnabled' => $userUpdateStruct->enabled !== null ? $userUpdateStruct->enabled : $loadedUser->enabled,
                'maxLogin' => $userUpdateStruct->maxLogin !== null ? (int)$userUpdateStruct->maxLogin : $loadedUser->maxLogin,
            ]);

            if ($userUpdateStruct->password) {
                $spiUser->passwordHash = $this->createPasswordHash(
                    $loadedUser->login,
                    $userUpdateStruct->password,
                    $this->settings['siteName'],
                    $this->settings['hashType']
                );
                $spiUser->hashAlgorithm = $this->settings['hashType'];
                $spiUser->passwordUpdatedAt = time();
            } else {
                $spiUser->passwordHash = $loadedUser->passwordHash;
                $spiUser->hashAlgorithm = $loadedUser->hashAlgorithm;
                $spiUser->passwordUpdatedAt = $loadedUser->passwordUpdatedAt ? $loadedUser->passwordUpdatedAt->getTimestamp() : null;
            }

            $this->userHandler->update($spiUser);

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
            throw new BadStateException('userGroup', 'user group has no main location or no locations');
        }

        if (in_array($loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId, $existingGroupIds)) {
            // user is already assigned to the user group
            throw new InvalidArgumentException('user', 'user is already in the given user group');
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
            throw new BadStateException('user', 'user has no locations, cannot unassign from group');
        }

        if ($loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId === null) {
            throw new BadStateException('userGroup', 'user group has no main location or no locations, cannot unassign');
        }

        foreach ($userLocations as $userLocation) {
            if ($userLocation->parentLocationId == $loadedGroup->getVersionInfo()->getContentInfo()->mainLocationId) {
                // Throw this specific BadState when we know argument is valid
                if (count($userLocations) === 1) {
                    throw new BadStateException('user', 'user only has one user group, cannot unassign from last group');
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

        throw new InvalidArgumentException('userGroup', 'user is not in the given user group');
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
                $this->repository->getContentService()->internalLoadContent(
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
                $this->repository->getContentService()->internalLoadContent(
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

        return new UserCreateStruct(
            [
                'contentType' => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'login' => $login,
                'email' => $email,
                'password' => $password,
                'enabled' => true,
                'fields' => [],
            ]
        );
    }

    /**
     * Instantiate a user group create class.
     *
     * @param string $mainLanguageCode The main language for the underlying content object
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType|null $contentType 5.x the content type for the underlying content object. In 4.x it is ignored and taken from the configuration
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
        $userFieldDefinition = null;
        foreach ($context->contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier === 'ezuser') {
                $userFieldDefinition = $fieldDefinition;
                break;
            }
        }

        if ($userFieldDefinition === null) {
            throw new ContentValidationException('Provided content type does not contain ezuser field type');
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
            $content = $this->repository->getContentService()->internalLoadContent(
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
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->fieldTypeIdentifier == 'ezuser') {
                return $fieldDefinition;
            }
        }

        return null;
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
     * Verifies if the provided login and password are valid.
     *
     * @deprecated since v7.5.5 in favour of verifyPasswordForSPIUser
     *
     * @param string $login User login
     * @param string $password User password
     * @param \eZ\Publish\SPI\Persistence\User $spiUser Loaded user handler
     *
     * @return bool return true if the login and password are sucessfully
     * validate and false, if not.
     */
    protected function verifyPassword($login, $password, $spiUser)
    {
        return $this->comparePasswordHashForSPIUser($login, $password, $spiUser);
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
        // In case of bcrypt let php's password functionality do it's magic
        if ($hashAlgorithm === APIUser::PASSWORD_HASH_BCRYPT ||
            $hashAlgorithm === APIUser::PASSWORD_HASH_PHP_DEFAULT
        ) {
            return password_verify($plainPassword, $passwordHash);
        }

        return $passwordHash === $this->createPasswordHash(
            $login,
            $plainPassword,
            $this->settings['siteName'],
            $hashAlgorithm
        );
    }

    /**
     * Returns password hash based on user data and site settings.
     *
     * @param string $login User login
     * @param string $password User password
     * @param string $site The name of the site
     * @param int $type Type of password to generate
     *
     * @return string Generated password hash
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the type is not recognized
     */
    protected function createPasswordHash($login, $password, $site, $type)
    {
        $deprecationWarningFormat = 'Password hash type %s is deprecated since 6.13.';

        switch ($type) {
            case APIUser::PASSWORD_HASH_MD5_PASSWORD:
                @trigger_error(sprintf($deprecationWarningFormat, 'PASSWORD_HASH_MD5_PASSWORD'), E_USER_DEPRECATED);

                return md5($password);

            case APIUser::PASSWORD_HASH_MD5_USER:
                @trigger_error(sprintf($deprecationWarningFormat, 'PASSWORD_HASH_MD5_USER'), E_USER_DEPRECATED);

                return md5("$login\n$password");

            case APIUser::PASSWORD_HASH_MD5_SITE:
                @trigger_error(sprintf($deprecationWarningFormat, 'PASSWORD_HASH_MD5_SITE'), E_USER_DEPRECATED);

                return md5("$login\n$password\n$site");

            case APIUser::PASSWORD_HASH_PLAINTEXT:
                @trigger_error(sprintf($deprecationWarningFormat, 'PASSWORD_HASH_PLAINTEXT'), E_USER_DEPRECATED);

                return $password;

            case APIUser::PASSWORD_HASH_BCRYPT:
                return password_hash($password, PASSWORD_BCRYPT);

            case APIUser::PASSWORD_HASH_PHP_DEFAULT:
                return password_hash($password, PASSWORD_DEFAULT);

            default:
                throw new InvalidArgumentException('type', "Password hash type '$type' is not recognized");
        }
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
