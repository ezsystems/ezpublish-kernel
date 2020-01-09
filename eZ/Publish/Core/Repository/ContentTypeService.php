<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\SPI\Persistence\Content\Type\Handler;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Exceptions\BadStateException as APIBadStateException;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandler;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct as APIContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as SPIContentTypeCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as SPIContentTypeGroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as SPIContentTypeGroupUpdateStruct;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\ContentTypeValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentTypeFieldDefinitionValidationException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\ValidationError;
use DateTime;
use Exception;

class ContentTypeService implements ContentTypeServiceInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\SPI\Persistence\Content\Type\Handler */
    protected $contentTypeHandler;

    /** @var \eZ\Publish\SPI\Persistence\User\Handler */
    protected $userHandler;

    /** @var array */
    protected $settings;

    /** @var \eZ\Publish\Core\Repository\Mapper\ContentDomainMapper */
    protected $contentDomainMapper;

    /** @var \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper */
    protected $contentTypeDomainMapper;

    /** @var \eZ\Publish\Core\FieldType\FieldTypeRegistry */
    protected $fieldTypeRegistry;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\User\Handler $userHandler
     * @param \eZ\Publish\Core\Repository\Mapper\ContentDomainMapper $contentDomainMapper
     * @param \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper $contentTypeDomainMapper
     * @param \eZ\Publish\Core\FieldType\FieldTypeRegistry $fieldTypeRegistry
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $contentTypeHandler,
        UserHandler $userHandler,
        Mapper\ContentDomainMapper $contentDomainMapper,
        Helper\ContentTypeDomainMapper $contentTypeDomainMapper,
        FieldTypeRegistry $fieldTypeRegistry,
        PermissionResolver $permissionResolver,
        array $settings = []
    ) {
        $this->repository = $repository;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->userHandler = $userHandler;
        $this->contentDomainMapper = $contentDomainMapper;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + [
            //'defaultSetting' => array(),
        ];
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Create a Content Type Group object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a group with the same identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct): APIContentTypeGroup
    {
        if (!$this->permissionResolver->canUser('class', 'create', $contentTypeGroupCreateStruct)) {
            throw new UnauthorizedException('ContentType', 'create');
        }

        try {
            $this->loadContentTypeGroupByIdentifier($contentTypeGroupCreateStruct->identifier);

            throw new InvalidArgumentException(
                '$contentTypeGroupCreateStruct',
                "A group with the identifier '{$contentTypeGroupCreateStruct->identifier}' already exists"
            );
        } catch (APINotFoundException $e) {
            // Do nothing
        }

        if ($contentTypeGroupCreateStruct->creationDate === null) {
            $timestamp = time();
        } else {
            $timestamp = $contentTypeGroupCreateStruct->creationDate->getTimestamp();
        }

        if ($contentTypeGroupCreateStruct->creatorId === null) {
            $userId = $this->permissionResolver->getCurrentUserReference()->getUserId();
        } else {
            $userId = $contentTypeGroupCreateStruct->creatorId;
        }

        $spiGroupCreateStruct = new SPIContentTypeGroupCreateStruct(
            [
                'identifier' => $contentTypeGroupCreateStruct->identifier,
                'created' => $timestamp,
                'modified' => $timestamp,
                'creatorId' => $userId,
                'modifierId' => $userId,
            ]
        );

        $this->repository->beginTransaction();
        try {
            $spiContentTypeGroup = $this->contentTypeHandler->createGroup(
                $spiGroupCreateStruct
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->contentTypeDomainMapper->buildContentTypeGroupDomainObject($spiContentTypeGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroup(int $contentTypeGroupId, array $prioritizedLanguages = []): APIContentTypeGroup
    {
        $spiGroup = $this->contentTypeHandler->loadGroup(
            $contentTypeGroupId
        );

        return $this->contentTypeDomainMapper->buildContentTypeGroupDomainObject($spiGroup, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroupByIdentifier(string $contentTypeGroupIdentifier, array $prioritizedLanguages = []): APIContentTypeGroup
    {
        $groups = $this->loadContentTypeGroups($prioritizedLanguages);

        foreach ($groups as $group) {
            if ($group->identifier === $contentTypeGroupIdentifier) {
                return $group;
            }
        }

        throw new NotFoundException('ContentTypeGroup', $contentTypeGroupIdentifier);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroups(array $prioritizedLanguages = []): iterable
    {
        $spiGroups = $this->contentTypeHandler->loadAllGroups();

        $groups = [];
        foreach ($spiGroups as $spiGroup) {
            $groups[] = $this->contentTypeDomainMapper->buildContentTypeGroupDomainObject($spiGroup, $prioritizedLanguages);
        }

        return $groups;
    }

    /**
     * Update a Content Type Group object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier (if set) already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function updateContentTypeGroup(APIContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentTypeGroup)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $loadedContentTypeGroup = $this->loadContentTypeGroup($contentTypeGroup->id);

        if ($contentTypeGroupUpdateStruct->identifier !== null
            && $contentTypeGroupUpdateStruct->identifier !== $loadedContentTypeGroup->identifier) {
            try {
                $this->loadContentTypeGroupByIdentifier($contentTypeGroupUpdateStruct->identifier);

                throw new InvalidArgumentException(
                    '$contentTypeGroupUpdateStruct->identifier',
                    'given identifier already exists'
                );
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        if ($contentTypeGroupUpdateStruct->modificationDate !== null) {
            $modifiedTimestamp = $contentTypeGroupUpdateStruct->modificationDate->getTimestamp();
        } else {
            $modifiedTimestamp = time();
        }

        $spiGroupUpdateStruct = new SPIContentTypeGroupUpdateStruct(
            [
                'id' => $loadedContentTypeGroup->id,
                'identifier' => $contentTypeGroupUpdateStruct->identifier === null ?
                    $loadedContentTypeGroup->identifier :
                    $contentTypeGroupUpdateStruct->identifier,
                'modified' => $modifiedTimestamp,
                'modifierId' => $contentTypeGroupUpdateStruct->modifierId === null ?
                    $this->permissionResolver->getCurrentUserReference()->getUserId() :
                    $contentTypeGroupUpdateStruct->modifierId,
            ]
        );

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->updateGroup(
                $spiGroupUpdateStruct
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Delete a Content Type Group.
     *
     * This method only deletes an content type group which has content types without any content instances
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If  a to be deleted content type has instances
     */
    public function deleteContentTypeGroup(APIContentTypeGroup $contentTypeGroup): void
    {
        if (!$this->permissionResolver->canUser('class', 'delete', $contentTypeGroup)) {
            throw new UnauthorizedException('ContentType', 'delete');
        }

        $loadedContentTypeGroup = $this->loadContentTypeGroup($contentTypeGroup->id);

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->deleteGroup(
                $loadedContentTypeGroup->id
            );
            $this->repository->commit();
        } catch (APIBadStateException $e) {
            $this->repository->rollback();
            throw new InvalidArgumentException(
                '$contentTypeGroup',
                'Content Type group contains Content Types',
                $e
            );
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Validates input ContentType create struct.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     */
    protected function validateInputContentTypeCreateStruct(APIContentTypeCreateStruct $contentTypeCreateStruct): void
    {
        // Required properties

        if ($contentTypeCreateStruct->identifier === null) {
            throw new InvalidArgumentException('$contentTypeCreateStruct', "Property 'identifier' is required");
        }

        if (!is_string($contentTypeCreateStruct->identifier)) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->identifier',
                'string',
                $contentTypeCreateStruct->identifier
            );
        }

        if ($contentTypeCreateStruct->identifier === '') {
            throw new InvalidArgumentValue(
                '$contentTypeCreateStruct->identifier',
                $contentTypeCreateStruct->identifier
            );
        }

        if ($contentTypeCreateStruct->mainLanguageCode === null) {
            throw new InvalidArgumentException('$contentTypeCreateStruct', "Property 'mainLanguageCode' is required");
        }

        if (!is_string($contentTypeCreateStruct->mainLanguageCode)) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->mainLanguageCode',
                'string',
                $contentTypeCreateStruct->mainLanguageCode
            );
        }

        if ($contentTypeCreateStruct->mainLanguageCode === '') {
            throw new InvalidArgumentValue(
                '$contentTypeCreateStruct->mainLanguageCode',
                $contentTypeCreateStruct->mainLanguageCode
            );
        }

        if ($contentTypeCreateStruct->names !== null) {
            $this->contentDomainMapper->validateTranslatedList(
                $contentTypeCreateStruct->names,
                '$contentTypeCreateStruct->names'
            );
        }

        if (!isset($contentTypeCreateStruct->names[$contentTypeCreateStruct->mainLanguageCode]) ||
            $contentTypeCreateStruct->names[$contentTypeCreateStruct->mainLanguageCode] === ''
        ) {
            throw new InvalidArgumentException(
                '$contentTypeCreateStruct->names',
                'At least one name in the main language is required'
            );
        }

        // Optional properties

        if ($contentTypeCreateStruct->descriptions !== null) {
            $this->contentDomainMapper->validateTranslatedList(
                $contentTypeCreateStruct->descriptions,
                '$contentTypeCreateStruct->descriptions'
            );
        }

        if ($contentTypeCreateStruct->defaultSortField !== null && !$this->contentDomainMapper->isValidLocationSortField($contentTypeCreateStruct->defaultSortField)) {
            throw new InvalidArgumentValue(
                '$contentTypeCreateStruct->defaultSortField',
                $contentTypeCreateStruct->defaultSortField
            );
        }

        if ($contentTypeCreateStruct->defaultSortOrder !== null && !$this->contentDomainMapper->isValidLocationSortOrder($contentTypeCreateStruct->defaultSortOrder)) {
            throw new InvalidArgumentValue(
                '$contentTypeCreateStruct->defaultSortOrder',
                $contentTypeCreateStruct->defaultSortOrder
            );
        }

        if ($contentTypeCreateStruct->creatorId !== null) {
            $this->repository->getUserService()->loadUser($contentTypeCreateStruct->creatorId);
        }

        if ($contentTypeCreateStruct->creationDate !== null && !$contentTypeCreateStruct->creationDate instanceof DateTime) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->creationDate',
                'DateTime',
                $contentTypeCreateStruct->creationDate
            );
        }

        if ($contentTypeCreateStruct->defaultAlwaysAvailable !== null && !is_bool($contentTypeCreateStruct->defaultAlwaysAvailable)) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->defaultAlwaysAvailable',
                'boolean',
                $contentTypeCreateStruct->defaultAlwaysAvailable
            );
        }

        if ($contentTypeCreateStruct->isContainer !== null && !is_bool($contentTypeCreateStruct->isContainer)) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->isContainer',
                'boolean',
                $contentTypeCreateStruct->isContainer
            );
        }

        if ($contentTypeCreateStruct->remoteId !== null && !is_string($contentTypeCreateStruct->remoteId)) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->remoteId',
                'string',
                $contentTypeCreateStruct->remoteId
            );
        }

        if ($contentTypeCreateStruct->nameSchema !== null && !is_string($contentTypeCreateStruct->nameSchema)) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->nameSchema',
                'string',
                $contentTypeCreateStruct->nameSchema
            );
        }

        if ($contentTypeCreateStruct->urlAliasSchema !== null && !is_string($contentTypeCreateStruct->urlAliasSchema)) {
            throw new InvalidArgumentType(
                '$contentTypeCreateStruct->urlAliasSchema',
                'string',
                $contentTypeCreateStruct->urlAliasSchema
            );
        }

        foreach ($contentTypeCreateStruct->fieldDefinitions as $key => $fieldDefinitionCreateStruct) {
            if (!$fieldDefinitionCreateStruct instanceof FieldDefinitionCreateStruct) {
                throw new InvalidArgumentType(
                    "\$contentTypeCreateStruct->fieldDefinitions[$key]",
                    'eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinitionCreateStruct',
                    $fieldDefinitionCreateStruct
                );
            }

            $this->validateInputFieldDefinitionCreateStruct(
                $fieldDefinitionCreateStruct,
                "\$contentTypeCreateStruct->fieldDefinitions[$key]"
            );
        }
    }

    /**
     * Validates input ContentTypeGroup array.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups
     */
    protected function validateInputContentTypeGroups(array $contentTypeGroups): void
    {
        if (empty($contentTypeGroups)) {
            throw new InvalidArgumentException(
                '$contentTypeGroups',
                'The argument must contain at least one Content Type group'
            );
        }

        foreach ($contentTypeGroups as $key => $contentTypeGroup) {
            if (!$contentTypeGroup instanceof APIContentTypeGroup) {
                throw new InvalidArgumentType(
                    "\$contentTypeGroups[{$key}]",
                    'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup',
                    $contentTypeGroup
                );
            }
        }
    }

    /**
     * Validates input FieldDefinitionCreateStruct.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @param FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     * @param string $argumentName
     */
    protected function validateInputFieldDefinitionCreateStruct(
        FieldDefinitionCreateStruct $fieldDefinitionCreateStruct,
        string $argumentName = '$fieldDefinitionCreateStruct'
    ): void {
        // Required properties

        if ($fieldDefinitionCreateStruct->fieldTypeIdentifier === null) {
            throw new InvalidArgumentException($argumentName, "Property 'fieldTypeIdentifier' is required");
        }

        if (!is_string($fieldDefinitionCreateStruct->fieldTypeIdentifier)) {
            throw new InvalidArgumentType(
                $argumentName . '->fieldTypeIdentifier',
                'string',
                $fieldDefinitionCreateStruct->fieldTypeIdentifier
            );
        }

        if ($fieldDefinitionCreateStruct->fieldTypeIdentifier === '') {
            throw new InvalidArgumentValue(
                $argumentName . '->fieldTypeIdentifier',
                $fieldDefinitionCreateStruct->fieldTypeIdentifier
            );
        }

        if ($fieldDefinitionCreateStruct->identifier === null) {
            throw new InvalidArgumentException($argumentName, "Property 'identifier' is required");
        }

        if (!is_string($fieldDefinitionCreateStruct->identifier)) {
            throw new InvalidArgumentType(
                $argumentName . '->identifier',
                'string',
                $fieldDefinitionCreateStruct->identifier
            );
        }

        if ($fieldDefinitionCreateStruct->identifier === '') {
            throw new InvalidArgumentValue(
                $argumentName . '->identifier',
                $fieldDefinitionCreateStruct->identifier
            );
        }

        // Optional properties

        if ($fieldDefinitionCreateStruct->names !== null) {
            $this->contentDomainMapper->validateTranslatedList(
                $fieldDefinitionCreateStruct->names,
                $argumentName . '->names'
            );
        }

        if ($fieldDefinitionCreateStruct->descriptions !== null) {
            $this->contentDomainMapper->validateTranslatedList(
                $fieldDefinitionCreateStruct->descriptions,
                $argumentName . '->descriptions'
            );
        }

        if ($fieldDefinitionCreateStruct->fieldGroup !== null && !is_string($fieldDefinitionCreateStruct->fieldGroup)) {
            throw new InvalidArgumentType(
                $argumentName . '->fieldGroup',
                'string',
                $fieldDefinitionCreateStruct->fieldGroup
            );
        }

        if ($fieldDefinitionCreateStruct->position !== null && !is_int($fieldDefinitionCreateStruct->position)) {
            throw new InvalidArgumentType(
                $argumentName . '->position',
                'integer',
                $fieldDefinitionCreateStruct->position
            );
        }

        if ($fieldDefinitionCreateStruct->isTranslatable !== null && !is_bool($fieldDefinitionCreateStruct->isTranslatable)) {
            throw new InvalidArgumentType(
                $argumentName . '->isTranslatable',
                'boolean',
                $fieldDefinitionCreateStruct->isTranslatable
            );
        }

        if ($fieldDefinitionCreateStruct->isRequired !== null && !is_bool($fieldDefinitionCreateStruct->isRequired)) {
            throw new InvalidArgumentType(
                $argumentName . '->isRequired',
                'boolean',
                $fieldDefinitionCreateStruct->isRequired
            );
        }

        if ($fieldDefinitionCreateStruct->isThumbnail !== null && !is_bool($fieldDefinitionCreateStruct->isThumbnail)) {
            throw new InvalidArgumentType(
                $argumentName . '->isThumbnail',
                'boolean',
                $fieldDefinitionCreateStruct->isThumbnail
            );
        }

        if ($fieldDefinitionCreateStruct->isInfoCollector !== null && !is_bool($fieldDefinitionCreateStruct->isInfoCollector)) {
            throw new InvalidArgumentType(
                $argumentName . '->isInfoCollector',
                'boolean',
                $fieldDefinitionCreateStruct->isInfoCollector
            );
        }

        if ($fieldDefinitionCreateStruct->isSearchable !== null && !is_bool($fieldDefinitionCreateStruct->isSearchable)) {
            throw new InvalidArgumentType(
                $argumentName . '->isSearchable',
                'boolean',
                $fieldDefinitionCreateStruct->isSearchable
            );
        }

        // These properties are of type 'mixed' and are validated separately by the corresponding field type
        // validatorConfiguration
        // fieldSettings
        // defaultValue
    }

    /**
     * Create a Content Type object.
     *
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException In case when
     *         - array of content type groups does not contain at least one content type group
     *         - identifier or remoteId in the content type create struct already exists
     *         - there is a duplicate field identifier in the content type create struct
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     *         if a field definition in the $contentTypeCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeValidationException
     *         if a multiple field definitions of a same singular type are given
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups Required array of {@link APIContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType(APIContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups): APIContentTypeDraft
    {
        if (!$this->permissionResolver->canUser('class', 'create', $contentTypeCreateStruct, $contentTypeGroups)) {
            throw new UnauthorizedException('ContentType', 'create');
        }

        // Prevent argument mutation
        $contentTypeCreateStruct = clone $contentTypeCreateStruct;
        $this->validateInputContentTypeCreateStruct($contentTypeCreateStruct);
        $this->validateInputContentTypeGroups($contentTypeGroups);
        $initialLanguageId = $this->repository->getContentLanguageService()->loadLanguage(
            $contentTypeCreateStruct->mainLanguageCode
        )->id;

        try {
            $this->contentTypeHandler->loadByIdentifier(
                $contentTypeCreateStruct->identifier
            );

            throw new InvalidArgumentException(
                '$contentTypeCreateStruct',
                "Another Content Type with identifier '{$contentTypeCreateStruct->identifier}' exists"
            );
        } catch (APINotFoundException $e) {
            // Do nothing
        }

        if ($contentTypeCreateStruct->remoteId !== null) {
            try {
                $this->contentTypeHandler->loadByRemoteId(
                    $contentTypeCreateStruct->remoteId
                );

                throw new InvalidArgumentException(
                    '$contentTypeCreateStruct',
                    "Another Content Type with remoteId '{$contentTypeCreateStruct->remoteId}' exists"
                );
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        $fieldDefinitionIdentifierSet = [];
        $fieldDefinitionPositionSet = [];
        foreach ($contentTypeCreateStruct->fieldDefinitions as $fieldDefinitionCreateStruct) {
            // Check for duplicate identifiers
            if (!isset($fieldDefinitionIdentifierSet[$fieldDefinitionCreateStruct->identifier])) {
                $fieldDefinitionIdentifierSet[$fieldDefinitionCreateStruct->identifier] = true;
            } else {
                throw new InvalidArgumentException(
                    '$contentTypeCreateStruct',
                    "The argument contains duplicate Field definition identifier '{$fieldDefinitionCreateStruct->identifier}'"
                );
            }

            // Check for duplicate positions
            if (!isset($fieldDefinitionPositionSet[$fieldDefinitionCreateStruct->position])) {
                $fieldDefinitionPositionSet[$fieldDefinitionCreateStruct->position] = true;
            } else {
                throw new InvalidArgumentException(
                    '$contentTypeCreateStruct',
                    "The argument contains duplicate Field definition position '{$fieldDefinitionCreateStruct->position}'"
                );
            }
        }

        $allValidationErrors = [];
        $spiFieldDefinitions = [];
        $fieldTypeIdentifierSet = [];
        foreach ($contentTypeCreateStruct->fieldDefinitions as $fieldDefinitionCreateStruct) {
            /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
            $fieldType = $this->fieldTypeRegistry->getFieldType(
                $fieldDefinitionCreateStruct->fieldTypeIdentifier
            );

            if ($fieldType->isSingular() && isset($fieldTypeIdentifierSet[$fieldDefinitionCreateStruct->fieldTypeIdentifier])) {
                throw new ContentTypeValidationException(
                    "Field Type '%identifier%' is singular and cannot be used more than once in a Content Type",
                    ['%identifier%' => $fieldDefinitionCreateStruct->fieldTypeIdentifier]
                );
            }

            $fieldTypeIdentifierSet[$fieldDefinitionCreateStruct->fieldTypeIdentifier] = true;

            $fieldType->applyDefaultSettings($fieldDefinitionCreateStruct->fieldSettings);
            $fieldType->applyDefaultValidatorConfiguration($fieldDefinitionCreateStruct->validatorConfiguration);
            $validationErrors = $this->validateFieldDefinitionCreateStruct(
                $fieldDefinitionCreateStruct,
                $fieldType
            );

            if (!empty($validationErrors)) {
                $allValidationErrors[$fieldDefinitionCreateStruct->identifier] = $validationErrors;
            }

            if (!empty($allValidationErrors)) {
                continue;
            }

            $spiFieldDefinitions[] = $this->contentTypeDomainMapper->buildSPIFieldDefinitionFromCreateStruct(
                $fieldDefinitionCreateStruct,
                $fieldType,
                $contentTypeCreateStruct->mainLanguageCode
            );
        }

        if (!empty($allValidationErrors)) {
            throw new ContentTypeFieldDefinitionValidationException($allValidationErrors);
        }

        $groupIds = array_map(
            function (APIContentTypeGroup $contentTypeGroup) {
                return $contentTypeGroup->id;
            },
            $contentTypeGroups
        );

        if ($contentTypeCreateStruct->creatorId === null) {
            $contentTypeCreateStruct->creatorId = $this->permissionResolver->getCurrentUserReference()->getUserId();
        }

        if ($contentTypeCreateStruct->creationDate === null) {
            $timestamp = time();
        } else {
            $timestamp = $contentTypeCreateStruct->creationDate->getTimestamp();
        }

        if ($contentTypeCreateStruct->remoteId === null) {
            $contentTypeCreateStruct->remoteId = $this->contentDomainMapper->getUniqueHash($contentTypeCreateStruct);
        }

        $spiContentTypeCreateStruct = new SPIContentTypeCreateStruct(
            [
                'identifier' => $contentTypeCreateStruct->identifier,
                'name' => $contentTypeCreateStruct->names,
                'status' => APIContentType::STATUS_DRAFT,
                'description' => $contentTypeCreateStruct->descriptions === null ?
                    [] :
                    $contentTypeCreateStruct->descriptions,
                'created' => $timestamp,
                'modified' => $timestamp,
                'creatorId' => $contentTypeCreateStruct->creatorId,
                'modifierId' => $contentTypeCreateStruct->creatorId,
                'remoteId' => $contentTypeCreateStruct->remoteId,
                'urlAliasSchema' => $contentTypeCreateStruct->urlAliasSchema === null ?
                    '' :
                    $contentTypeCreateStruct->urlAliasSchema,
                'nameSchema' => $contentTypeCreateStruct->nameSchema === null ?
                    '' :
                    $contentTypeCreateStruct->nameSchema,
                'isContainer' => $contentTypeCreateStruct->isContainer === null ?
                    false :
                    $contentTypeCreateStruct->isContainer,
                'initialLanguageId' => $initialLanguageId,
                'sortField' => $contentTypeCreateStruct->defaultSortField === null ?
                    Location::SORT_FIELD_PUBLISHED :
                    $contentTypeCreateStruct->defaultSortField,
                'sortOrder' => $contentTypeCreateStruct->defaultSortOrder === null ?
                    Location::SORT_ORDER_DESC :
                    $contentTypeCreateStruct->defaultSortOrder,
                'groupIds' => $groupIds,
                'fieldDefinitions' => $spiFieldDefinitions,
                'defaultAlwaysAvailable' => $contentTypeCreateStruct->defaultAlwaysAvailable,
            ]
        );

        $this->repository->beginTransaction();
        try {
            $spiContentType = $this->contentTypeHandler->create(
                $spiContentTypeCreateStruct
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->contentTypeDomainMapper->buildContentTypeDraftDomainObject($spiContentType);
    }

    /**
     * Validates FieldDefinitionCreateStruct.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    protected function validateFieldDefinitionCreateStruct(FieldDefinitionCreateStruct $fieldDefinitionCreateStruct, SPIFieldType $fieldType): array
    {
        $validationErrors = [];

        if ($fieldDefinitionCreateStruct->isSearchable && !$fieldType->isSearchable()) {
            $validationErrors[] = new ValidationError(
                "FieldType '{$fieldDefinitionCreateStruct->fieldTypeIdentifier}' is not searchable"
            );
        }

        return array_merge(
            $validationErrors,
            $fieldType->validateValidatorConfiguration($fieldDefinitionCreateStruct->validatorConfiguration),
            $fieldType->validateFieldSettings($fieldDefinitionCreateStruct->fieldSettings)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentType(int $contentTypeId, array $prioritizedLanguages = []): ContentType
    {
        $spiContentType = $this->contentTypeHandler->load(
            $contentTypeId,
            SPIContentType::STATUS_DEFINED
        );

        return $this->contentTypeDomainMapper->buildContentTypeDomainObject(
            $spiContentType,
            $prioritizedLanguages
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeByIdentifier(string $identifier, array $prioritizedLanguages = []): ContentType
    {
        $spiContentType = $this->contentTypeHandler->loadByIdentifier(
            $identifier
        );

        return $this->contentTypeDomainMapper->buildContentTypeDomainObject(
            $spiContentType,
            $prioritizedLanguages
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeByRemoteId(string $remoteId, array $prioritizedLanguages = []): ContentType
    {
        $spiContentType = $this->contentTypeHandler->loadByRemoteId($remoteId);

        return $this->contentTypeDomainMapper->buildContentTypeDomainObject(
            $spiContentType,
            $prioritizedLanguages
        );
    }

    /**
     * Get a Content Type object draft by id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param int $contentTypeId
     * @param bool $ignoreOwnership if true, method will return draft even if the owner is different than currently logged in user
     *
     * @todo Use another exception when user of draft is someone else
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function loadContentTypeDraft(int $contentTypeId, bool $ignoreOwnership = false): APIContentTypeDraft
    {
        $spiContentType = $this->contentTypeHandler->load(
            $contentTypeId,
            SPIContentType::STATUS_DRAFT
        );

        if (!$ignoreOwnership && $spiContentType->modifierId != $this->permissionResolver->getCurrentUserReference()->getUserId()) {
            throw new NotFoundException('The Content Type is owned by someone else', $contentTypeId);
        }

        return $this->contentTypeDomainMapper->buildContentTypeDraftDomainObject($spiContentType);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeList(array $contentTypeIds, array $prioritizedLanguages = []): iterable
    {
        $spiContentTypes = $this->contentTypeHandler->loadContentTypeList($contentTypeIds);
        $contentTypes = [];

        // @todo We could bulk load content type group proxies involved in the future & pass those relevant per type to mapper
        foreach ($spiContentTypes as $spiContentType) {
            $contentTypes[$spiContentType->id] = $this->contentTypeDomainMapper->buildContentTypeDomainObject(
                $spiContentType,
                $prioritizedLanguages
            );
        }

        return $contentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypes(APIContentTypeGroup $contentTypeGroup, array $prioritizedLanguages = []): iterable
    {
        $spiContentTypes = $this->contentTypeHandler->loadContentTypes(
            $contentTypeGroup->id,
            SPIContentType::STATUS_DEFINED
        );
        $contentTypes = [];

        foreach ($spiContentTypes as $spiContentType) {
            $contentTypes[] = $this->contentTypeDomainMapper->buildContentTypeDomainObject(
                $spiContentType,
                $prioritizedLanguages
            );
        }

        return $contentTypes;
    }

    /**
     * Creates a draft from an existing content type.
     *
     * This is a complete copy of the content
     * type which has the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there is already a draft assigned to another user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentTypeDraft(APIContentType $contentType): APIContentTypeDraft
    {
        if (!$this->permissionResolver->canUser('class', 'create', $contentType)) {
            throw new UnauthorizedException('ContentType', 'create');
        }

        try {
            $this->contentTypeHandler->load(
                $contentType->id,
                SPIContentType::STATUS_DRAFT
            );

            throw new BadStateException(
                '$contentType',
                'Draft of the Content Type already exists'
            );
        } catch (APINotFoundException $e) {
            $this->repository->beginTransaction();
            try {
                $spiContentType = $this->contentTypeHandler->createDraft(
                    $this->permissionResolver->getCurrentUserReference()->getUserId(),
                    $contentType->id
                );
                $this->repository->commit();
            } catch (Exception $e) {
                $this->repository->rollback();
                throw $e;
            }
        }

        return $this->contentTypeDomainMapper->buildContentTypeDraftDomainObject($spiContentType);
    }

    /**
     * Update a Content Type object.
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier or remoteId already exists
     *         or there is no draft assigned to the authenticated user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function updateContentTypeDraft(APIContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentTypeDraft)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        try {
            $loadedContentTypeDraft = $this->loadContentTypeDraft($contentTypeDraft->id);
        } catch (APINotFoundException $e) {
            throw new InvalidArgumentException(
                '$contentTypeDraft',
                'There is no Content Type draft assigned to the authenticated user',
                $e
            );
        }

        if ($contentTypeUpdateStruct->identifier !== null
            && $contentTypeUpdateStruct->identifier != $loadedContentTypeDraft->identifier) {
            try {
                $this->loadContentTypeByIdentifier($contentTypeUpdateStruct->identifier);

                throw new InvalidArgumentException(
                    '$contentTypeUpdateStruct',
                    "Another Content Type with identifier '{$contentTypeUpdateStruct->identifier}' exists"
                );
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        if ($contentTypeUpdateStruct->remoteId !== null
            && $contentTypeUpdateStruct->remoteId != $loadedContentTypeDraft->remoteId) {
            try {
                $this->loadContentTypeByRemoteId($contentTypeUpdateStruct->remoteId);

                throw new InvalidArgumentException(
                    '$contentTypeUpdateStruct',
                    "Another Content Type with remoteId '{$contentTypeUpdateStruct->remoteId}' exists"
                );
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        }

        //Merge new translations into existing before update
        $contentTypeUpdateStruct->names = array_merge($contentTypeDraft->getNames(), $contentTypeUpdateStruct->names ?? []);
        $contentTypeUpdateStruct->descriptions = array_merge($contentTypeDraft->getDescriptions(), $contentTypeUpdateStruct->descriptions ?? []);

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->update(
                $contentTypeDraft->id,
                $contentTypeDraft->status,
                $this->contentTypeDomainMapper->buildSPIContentTypeUpdateStruct(
                    $loadedContentTypeDraft,
                    $contentTypeUpdateStruct,
                    $this->permissionResolver->getCurrentUserReference()
                )
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Delete a Content Type object.
     *
     * Deletes a content type if it has no instances. If content type in state STATUS_DRAFT is
     * given, only the draft content type will be deleted. Otherwise, if content type in state
     * STATUS_DEFINED is given, all content type data will be deleted.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there exist content objects of this type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     */
    public function deleteContentType(APIContentType $contentType): void
    {
        if (!$this->permissionResolver->canUser('class', 'delete', $contentType)) {
            throw new UnauthorizedException('ContentType', 'delete');
        }

        $this->repository->beginTransaction();
        try {
            if (!$contentType instanceof APIContentTypeDraft) {
                $this->contentTypeHandler->delete(
                    $contentType->id,
                    APIContentTypeDraft::STATUS_DEFINED
                );
            }

            $this->contentTypeHandler->delete(
                $contentType->id,
                APIContentTypeDraft::STATUS_DRAFT
            );

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Copy Type incl fields and groupIds to a new Type object.
     *
     * New Type will have $creator as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current-user is not allowed to copy a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\User\User $creator if null the current-user is used
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function copyContentType(APIContentType $contentType, User $creator = null): ContentType
    {
        if (!$this->permissionResolver->canUser('class', 'create', $contentType)) {
            throw new UnauthorizedException('ContentType', 'create');
        }

        if (empty($creator)) {
            $creator = $this->permissionResolver->getCurrentUserReference();
        }

        $this->repository->beginTransaction();
        try {
            $spiContentType = $this->contentTypeHandler->copy(
                $creator->getUserId(),
                $contentType->id,
                SPIContentType::STATUS_DEFINED
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadContentType($spiContentType->id);
    }

    /**
     * Assigns a content type to a content type group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type is already assigned the given group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function assignContentTypeGroup(APIContentType $contentType, APIContentTypeGroup $contentTypeGroup): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentType)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $spiContentType = $this->contentTypeHandler->load(
            $contentType->id,
            $contentType->status
        );

        if (in_array($contentTypeGroup->id, $spiContentType->groupIds)) {
            throw new InvalidArgumentException(
                '$contentTypeGroup',
                'The provided Content Type is already assigned to the Content Type group'
            );
        }

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->link(
                $contentTypeGroup->id,
                $contentType->id,
                $contentType->status
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Unassign a content type from a group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to link a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type is not assigned this the given group.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If $contentTypeGroup is the last group assigned to the content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function unassignContentTypeGroup(APIContentType $contentType, APIContentTypeGroup $contentTypeGroup): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentType, [$contentTypeGroup])) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $spiContentType = $this->contentTypeHandler->load(
            $contentType->id,
            $contentType->status
        );

        if (!in_array($contentTypeGroup->id, $spiContentType->groupIds)) {
            throw new InvalidArgumentException(
                '$contentTypeGroup',
                'The provided Content Type is not assigned the Content Type group'
            );
        }

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->unlink(
                $contentTypeGroup->id,
                $contentType->id,
                $contentType->status
            );
            $this->repository->commit();
        } catch (APIBadStateException $e) {
            $this->repository->rollback();
            throw new BadStateException(
                '$contentType',
                'The provided Content Type group is the last group assigned to the Content Type',
                $e
            );
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Adds a new field definition to an existing content type.
     *
     * The content type must be in state DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the identifier in already exists in the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException
     *         if a field definition in the $contentTypeCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If field definition of the same non-repeatable type is being
     *                                                                 added to the ContentType that already contains one
     *                                                                 or field definition that can't be added to a ContentType that
     *                                                                 has Content instances is being added to such ContentType
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function addFieldDefinition(APIContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentTypeDraft)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $this->validateInputFieldDefinitionCreateStruct($fieldDefinitionCreateStruct);
        $loadedContentTypeDraft = $this->loadContentTypeDraft($contentTypeDraft->id);

        if ($loadedContentTypeDraft->hasFieldDefinition($fieldDefinitionCreateStruct->identifier)) {
            throw new InvalidArgumentException(
                '$fieldDefinitionCreateStruct',
                "Another Field definition with identifier '{$fieldDefinitionCreateStruct->identifier}' exists in the Content Type"
            );
        }
        //Fill default translations with default value for mainLanguageCode with fallback if no exist
        if (is_array($fieldDefinitionCreateStruct->names)) {
            foreach ($contentTypeDraft->languageCodes as $languageCode) {
                if (!array_key_exists($languageCode, $fieldDefinitionCreateStruct->names)) {
                    $fieldDefinitionCreateStruct->names[$languageCode] = $fieldDefinitionCreateStruct->names[$contentTypeDraft->mainLanguageCode] ?? reset($fieldDefinitionCreateStruct->names);
                }
            }
        }

        /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
        $fieldType = $this->fieldTypeRegistry->getFieldType(
            $fieldDefinitionCreateStruct->fieldTypeIdentifier
        );

        $fieldType->applyDefaultSettings($fieldDefinitionCreateStruct->fieldSettings);
        $fieldType->applyDefaultValidatorConfiguration($fieldDefinitionCreateStruct->validatorConfiguration);
        $validationErrors = $this->validateFieldDefinitionCreateStruct($fieldDefinitionCreateStruct, $fieldType);
        if (!empty($validationErrors)) {
            $validationErrors = [$fieldDefinitionCreateStruct->identifier => $validationErrors];
            throw new ContentTypeFieldDefinitionValidationException($validationErrors);
        }

        if ($fieldType->isSingular()) {
            if ($loadedContentTypeDraft->hasFieldDefinitionOfType($fieldDefinitionCreateStruct->fieldTypeIdentifier)) {
                throw new BadStateException(
                    '$contentTypeDraft',
                    "The Content Type already contains a Field definition of the singular Field Type '{$fieldDefinitionCreateStruct->fieldTypeIdentifier}'"
                );
            }
        }

        if ($fieldType->onlyEmptyInstance() && $this->contentTypeHandler->getContentCount($loadedContentTypeDraft->id)
        ) {
            throw new BadStateException(
                '$contentTypeDraft',
                "A Field definition of the '{$fieldDefinitionCreateStruct->fieldTypeIdentifier}' Field Type cannot be added because the Content Type already has Content items"
            );
        }

        $spiFieldDefinition = $this->contentTypeDomainMapper->buildSPIFieldDefinitionFromCreateStruct(
            $fieldDefinitionCreateStruct,
            $fieldType,
            $contentTypeDraft->mainLanguageCode
        );

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->addFieldDefinition(
                $contentTypeDraft->id,
                $contentTypeDraft->status,
                $spiFieldDefinition
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given field definition does not belong to the given type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function removeFieldDefinition(APIContentTypeDraft $contentTypeDraft, APIFieldDefinition $fieldDefinition): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentTypeDraft)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $loadedFieldDefinition = $this->loadContentTypeDraft(
            $contentTypeDraft->id
        )->getFieldDefinition(
            $fieldDefinition->identifier
        );

        if (empty($loadedFieldDefinition) || $loadedFieldDefinition->id != $fieldDefinition->id) {
            throw new InvalidArgumentException(
                '$fieldDefinition',
                'The given Field definition does not belong to the Content Type'
            );
        }

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->removeFieldDefinition(
                $contentTypeDraft->id,
                SPIContentType::STATUS_DRAFT,
                $fieldDefinition->id
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Update a field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     *                                                                        If the given identifier is used in an existing field of the given content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition(APIContentTypeDraft $contentTypeDraft, APIFieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentTypeDraft)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $loadedContentTypeDraft = $this->loadContentTypeDraft($contentTypeDraft->id);
        $foundFieldId = false;
        foreach ($loadedContentTypeDraft->fieldDefinitions as $existingFieldDefinition) {
            if ($existingFieldDefinition->id == $fieldDefinition->id) {
                $foundFieldId = true;
            } elseif ($existingFieldDefinition->identifier == $fieldDefinitionUpdateStruct->identifier) {
                throw new InvalidArgumentException(
                    '$fieldDefinitionUpdateStruct',
                    "Another Field definition with identifier '{$fieldDefinitionUpdateStruct->identifier}' exists in the Content Type"
                );
            }
        }
        if (!$foundFieldId) {
            throw new InvalidArgumentException(
                '$fieldDefinition',
                'The given Field definition does not belong to the Content Type'
            );
        }

        $spiFieldDefinition = $this->contentTypeDomainMapper->buildSPIFieldDefinitionFromUpdateStruct(
            $fieldDefinitionUpdateStruct,
            $fieldDefinition,
            $contentTypeDraft->mainLanguageCode
        );

        $this->repository->beginTransaction();
        try {
            $this->contentTypeHandler->updateFieldDefinition(
                $contentTypeDraft->id,
                SPIContentType::STATUS_DRAFT,
                $spiFieldDefinition
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Publish the content type and update content objects.
     *
     * This method updates content objects, depending on the changed field definitions.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If the content type has no draft
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type has no field definitions
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     */
    public function publishContentTypeDraft(APIContentTypeDraft $contentTypeDraft): void
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentTypeDraft)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        try {
            $loadedContentTypeDraft = $this->loadContentTypeDraft($contentTypeDraft->id);
        } catch (APINotFoundException $e) {
            throw new BadStateException(
                '$contentTypeDraft',
                'The Content Type does not have a draft.',
                $e
            );
        }

        if ($loadedContentTypeDraft->getFieldDefinitions()->isEmpty()) {
            throw new InvalidArgumentException(
                '$contentTypeDraft',
                'The Content Type draft should have at least one Field definition.'
            );
        }

        $this->repository->beginTransaction();
        try {
            if (empty($loadedContentTypeDraft->nameSchema)) {
                $fieldDefinitions = $loadedContentTypeDraft->getFieldDefinitions();
                $this->contentTypeHandler->update(
                    $contentTypeDraft->id,
                    $contentTypeDraft->status,
                    $this->contentTypeDomainMapper->buildSPIContentTypeUpdateStruct(
                        $loadedContentTypeDraft,
                        new ContentTypeUpdateStruct(
                            [
                                'nameSchema' => '<' . $fieldDefinitions[0]->identifier . '>',
                            ]
                        ),
                        $this->permissionResolver->getCurrentUserReference()
                    )
                );
            }

            $this->contentTypeHandler->publish(
                $loadedContentTypeDraft->id
            );
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Instantiates a new content type group create class.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue if given identifier is not a string
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct(string $identifier): ContentTypeGroupCreateStruct
    {
        return new ContentTypeGroupCreateStruct(
            [
                'identifier' => $identifier,
            ]
        );
    }

    /**
     * Instantiates a new content type create class.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue if given identifier is not a string
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct(string $identifier): APIContentTypeCreateStruct
    {
        if (!is_string($identifier)) {
            throw new InvalidArgumentValue('$identifier', $identifier);
        }

        return new ContentTypeCreateStruct(
            [
                'identifier' => $identifier,
            ]
        );
    }

    /**
     * Instantiates a new content type update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct(): ContentTypeUpdateStruct
    {
        return new ContentTypeUpdateStruct();
    }

    /**
     * Instantiates a new content type update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct(): ContentTypeGroupUpdateStruct
    {
        return new ContentTypeGroupUpdateStruct();
    }

    /**
     * Instantiates a field definition create struct.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue if given identifier is not a string
     *          or given fieldTypeIdentifier is not a string
     *
     * @param string $fieldTypeIdentifier the required field type identifier
     * @param string $identifier the required identifier for the field definition
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct(string $identifier, string $fieldTypeIdentifier): FieldDefinitionCreateStruct
    {
        return new FieldDefinitionCreateStruct(
            [
                'identifier' => $identifier,
                'fieldTypeIdentifier' => $fieldTypeIdentifier,
            ]
        );
    }

    /**
     * Instantiates a field definition update class.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct(): FieldDefinitionUpdateStruct
    {
        return new FieldDefinitionUpdateStruct();
    }

    /**
     * Returns true if the given content type $contentType has content instances.
     *
     * @since 6.0.1
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return bool
     */
    public function isContentTypeUsed(APIContentType $contentType): bool
    {
        return $this->contentTypeHandler->getContentCount($contentType->id) > 0;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function removeContentTypeTranslation(APIContentTypeDraft $contentTypeDraft, string $languageCode): APIContentTypeDraft
    {
        if (!$this->permissionResolver->canUser('class', 'update', $contentTypeDraft)) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $this->repository->beginTransaction();
        try {
            $contentType = $this->contentTypeHandler->removeContentTypeTranslation(
                $contentTypeDraft->id,
                $languageCode
            );

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->contentTypeDomainMapper->buildContentTypeDraftDomainObject($contentType);
    }

    public function deleteUserDrafts(int $userId): void
    {
        try {
            $this->userHandler->load($userId);
        } catch (APINotFoundException $e) {
            $this->contentTypeHandler->deleteByUserAndStatus($userId, ContentType::STATUS_DRAFT);

            return;
        }

        if ($this->repository->getPermissionResolver()->hasAccess('class', 'delete') !== true) {
            throw new UnauthorizedException('ContentType', 'update');
        }

        $this->contentTypeHandler->deleteByUserAndStatus($userId, ContentType::STATUS_DRAFT);
    }
}
