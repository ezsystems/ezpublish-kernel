<?php
/**
 * File containing the eZ\Publish\Core\Repository\ContentTypeService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Content\Type\Handler,
    eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException,
    eZ\Publish\API\Repository\Exceptions\BadStateException as APIBadStateException,
    eZ\Publish\API\Repository\Values\User\User,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct,
    eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType,
    eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft,
    eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup,
    eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct,
    eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct as APIContentTypeCreateStruct,
    eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct,
    eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct,
    eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup,
    eZ\Publish\Core\Repository\Values\ContentType\ContentType,
    eZ\Publish\Core\Repository\Values\ContentType\ContentTypeDraft,
    eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct,
    eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\SPI\Persistence\Content\Type as SPIContentType,
    eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as SPIContentTypeCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct as SPIContentTypeUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as SPIFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\Type\Group as SPIContentTypeGroup,
    eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as SPIContentTypeGroupCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as SPIContentTypeGroupUpdateStruct,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\BadStateException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\ContentTypeFieldDefinitionValidationException,
    eZ\Publish\Core\Base\Exceptions\UnauthorizedException,
    DateTime,
    Exception;

/**
 * @example Examples/contenttype.php
 *
 * @package eZ\Publish\Core\Repository
 */
class ContentTypeService implements ContentTypeServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $contentTypeHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->settings = $settings + array(// Union makes sure default settings are ignored if provided in argument
            //'defaultSetting' => array(),
        );
    }

    /**
     * Create a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a group with the same identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function createContentTypeGroup( ContentTypeGroupCreateStruct  $contentTypeGroupCreateStruct )
    {
        if ( $this->repository->hasAccess( 'class', 'create' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'create' );

        try
        {
            $this->loadContentTypeGroupByIdentifier( $contentTypeGroupCreateStruct->identifier );

            throw new InvalidArgumentException(
                "\$contentTypeGroupCreateStruct",
                "A group with the identifier '{$contentTypeGroupCreateStruct->identifier}' already exists"
            );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        if ( $contentTypeGroupCreateStruct->creationDate === null )
            $timestamp = time();
        else
            $timestamp = $contentTypeGroupCreateStruct->creationDate->getTimestamp();

        if ( $contentTypeGroupCreateStruct->creatorId === null )
            $userId = $this->repository->getCurrentUser()->id;
        else
            $userId = $contentTypeGroupCreateStruct->creatorId;

        $spiGroupCreateStruct = new SPIContentTypeGroupCreateStruct(
            array(
                "name" => $contentTypeGroupCreateStruct->names,
                "description" => $contentTypeGroupCreateStruct->descriptions,
                "identifier" => $contentTypeGroupCreateStruct->identifier,
                "created" => $timestamp,
                "modified" => $timestamp,
                "creatorId" => $userId,
                "modifierId" => $userId
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $spiContentTypeGroup = $this->contentTypeHandler->createGroup(
                $spiGroupCreateStruct
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildContentTypeGroupDomainObject( $spiContentTypeGroup );
    }

    /**
     * Get a Content Type Group object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param int $contentTypeGroupId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup( $contentTypeGroupId )
    {
        if ( !is_numeric( $contentTypeGroupId ) )
        {
            throw new InvalidArgumentValue( '$contentTypeGroupId', $contentTypeGroupId );
        }

        $spiGroup = $this->contentTypeHandler->loadGroup(
            $contentTypeGroupId
        );

        return $this->buildContentTypeGroupDomainObject( $spiGroup );
    }

    /**
     * Get a Content Type Group object by identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If group can not be found
     *
     * @param string $contentTypeGroupIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroupByIdentifier( $contentTypeGroupIdentifier )
    {
        $groups = $this->loadContentTypeGroups();

        foreach ( $groups as $group )
        {
            if ( $group->identifier === $contentTypeGroupIdentifier )
            {
                return $group;
            }
        }

        throw new NotFoundException( 'ContentTypeGroup', $contentTypeGroupIdentifier );
    }

    /**
     * Get all Content Type Groups
     *
     * @return \eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function loadContentTypeGroups()
    {
        $spiGroups = $this->contentTypeHandler->loadAllGroups();

        $groups = array();
        foreach ( $spiGroups as $spiGroup )
        {
            $groups[] = $this->buildContentTypeGroupDomainObject( $spiGroup );
        }

        return $groups;
    }

    /**
     * Update a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier (if set) already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function updateContentTypeGroup( APIContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        try
        {
            $this->loadContentTypeGroupByIdentifier( $contentTypeGroupUpdateStruct->identifier );

            throw new InvalidArgumentException(
                '$contentTypeGroupUpdateStruct->identifier',
                "given identifier already exists"
            );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        if ( $contentTypeGroupUpdateStruct->modificationDate !== null )
        {
            $modifiedTimestamp = $contentTypeGroupUpdateStruct->modificationDate->getTimestamp();
        }
        else
        {
            $modifiedTimestamp = time();
        }

        if ( $contentTypeGroupUpdateStruct->modifierId === null )
        {
            $contentTypeGroupUpdateStruct->modifierId = $this->repository->getCurrentUser()->id;
        }

        $spiGroupUpdateStruct = new SPIContentTypeGroupUpdateStruct(
            array(
                "id" => $contentTypeGroup->id,
                "name" => $contentTypeGroupUpdateStruct->names,
                "description" => $contentTypeGroupUpdateStruct->descriptions,
                "identifier" => $contentTypeGroupUpdateStruct->identifier,
                "modified" => $modifiedTimestamp,
                "modifierId" => $contentTypeGroupUpdateStruct->modifierId
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->updateGroup(
                $spiGroupUpdateStruct
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
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
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function deleteContentTypeGroup( APIContentTypeGroup $contentTypeGroup )
    {
        if ( $this->repository->hasAccess( 'class', 'delete' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'delete' );

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->deleteGroup(
                $contentTypeGroup->id
            );
            $this->repository->commit();
        }
        catch ( APIBadStateException $e )
        {
            $this->repository->rollback();
            throw new InvalidArgumentException(
                "\$contentTypeGroup",
                "Content type group has content type instances",
                $e
            );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Builds a ContentTypeGroup domain object from value object returned by persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group $spiGroup
     *
     * @return \eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup
     */
    protected function buildContentTypeGroupDomainObject( SPIContentTypeGroup $spiGroup )
    {
        return new ContentTypeGroup(
            array(
                "id" => $spiGroup->id,
                "identifier" => $spiGroup->identifier,
                "creationDate" => $this->getDateTime( $spiGroup->created ),
                "modificationDate" => $this->getDateTime( $spiGroup->modified ),
                "creatorId" => $spiGroup->creatorId,
                "modifierId" => $spiGroup->modifierId,
                "names" => $spiGroup->name,
                "descriptions" => $spiGroup->description
            )
        );
    }

    /**
     *
     *
     * @param int|null $timestamp
     *
     * @return \DateTime|null
     */
    protected function getDateTime( $timestamp )
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp( $timestamp );
        return $dateTime;
    }

    /**
     * Create a Content Type object.
     *
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException In case when
     *         - array of content type groups does not contain at least one content type group
     *         - identifier or remoteId in the content type create struct already exists
     *         - there is a duplicate field identifier in the content type create struct
     *         - content type create struct does not contain at least one field definition create struct
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType( APIContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups )
    {
        if ( count( $contentTypeGroups ) === 0 )
        {
            throw new InvalidArgumentException(
                "\$contentTypeGroups",
                "Argument must contain at least one ContentTypeGroup"
            );
        }

        try
        {
            $this->contentTypeHandler->loadByIdentifier(
                $contentTypeCreateStruct->identifier
            );

            throw new InvalidArgumentException(
                "\$contentTypeCreateStruct",
                "Another ContentType with identifier '{$contentTypeCreateStruct->identifier}' exists"
            );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        try
        {
            $this->contentTypeHandler->loadByRemoteId(
                $contentTypeCreateStruct->remoteId
            );

            throw new InvalidArgumentException(
                "\$contentTypeCreateStruct",
                "Another ContentType with remoteId '{$contentTypeCreateStruct->remoteId}' exists"
            );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        if ( count( $contentTypeCreateStruct->fieldDefinitions ) === 0 )
        {
            throw new InvalidArgumentException(
                "\$contentTypeCreateStruct",
                "Argument must contain at least one FieldDefinitionCreateStruct"
            );
        }

        $fieldDefinitionIdentifierCache = array();
        foreach ( $contentTypeCreateStruct->fieldDefinitions as $fieldDefinitionCreateStruct )
        {
            if ( !isset( $fieldDefinitionIdentifierCache[$fieldDefinitionCreateStruct->identifier] ) )
            {
                $fieldDefinitionIdentifierCache[$fieldDefinitionCreateStruct->identifier] = true;
            }
            else
            {
                throw new InvalidArgumentException(
                    "\$contentTypeCreateStruct",
                    "Argument contains duplicate field definition identifier '{$fieldDefinitionCreateStruct->identifier}'"
                );
            }
        }

        if ( $contentTypeCreateStruct->creatorId === null )
        {
            $userId = $this->repository->getCurrentUser()->id;
        }
        else
        {
            $userId = $contentTypeCreateStruct->creatorId;
        }

        if ( $contentTypeCreateStruct->creationDate === null )
        {
            $timestamp = time();
        }
        else
        {
            $timestamp = $contentTypeCreateStruct->creationDate->getTimestamp();
        }

        if ( $contentTypeCreateStruct->remoteId === null )
        {
            $contentTypeCreateStruct->remoteId = md5( uniqid( get_class( $contentTypeCreateStruct ), true ) );
        }

        $initialLanguageId = $this->repository->getContentLanguageService()->loadLanguage(
            $contentTypeCreateStruct->mainLanguageCode
        )->id;
        $groupIds = array_map(
            function( $contentTypeGroup )
            {
                return $contentTypeGroup->id;
            },
            $contentTypeGroups
        );
        $fieldDefinitions = array();
        foreach ( $contentTypeCreateStruct->fieldDefinitions as $fieldDefinitionCreateStruct )
        {
            $fieldDefinitions[] = $this->buildSPIFieldDefinitionCreate( $fieldDefinitionCreateStruct );
        }

        $spiContentTypeCreateStruct = new SPIContentTypeCreateStruct(
            array(
                "name" => $contentTypeCreateStruct->names,
                "status" => APIContentType::STATUS_DRAFT,
                "description" => $contentTypeCreateStruct->descriptions,
                "identifier" => $contentTypeCreateStruct->identifier,
                "created" => $timestamp,
                "modified" => $timestamp,
                "creatorId" => $userId,
                "modifierId" => $userId,
                "remoteId" => $contentTypeCreateStruct->remoteId,
                "urlAliasSchema" => $contentTypeCreateStruct->urlAliasSchema,
                "nameSchema" => $contentTypeCreateStruct->nameSchema,
                "isContainer" => $contentTypeCreateStruct->isContainer,
                "initialLanguageId" => $initialLanguageId,
                "sortField" => $contentTypeCreateStruct->defaultSortField,
                "sortOrder" => $contentTypeCreateStruct->defaultSortOrder,
                "groupIds" => $groupIds,
                "fieldDefinitions" => $fieldDefinitions,
                "defaultAlwaysAvailable" => $contentTypeCreateStruct->defaultAlwaysAvailable
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $spiContentType = $this->contentTypeHandler->create(
                $spiContentTypeCreateStruct
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildContentTypeDraftDomainObject( $spiContentType );
    }

    /**
     * Builds SPIFieldDefinition object using API FieldDefinitionCreateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException if validator configuration or
     *         field setting do not validate
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function buildSPIFieldDefinitionCreate( FieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        $spiFieldDefinition = new SPIFieldDefinition(
            array(
                "id" => null,
                "name" => $fieldDefinitionCreateStruct->names,
                "description" => $fieldDefinitionCreateStruct->descriptions,
                "identifier" => $fieldDefinitionCreateStruct->identifier,
                "fieldGroup" => (string)$fieldDefinitionCreateStruct->fieldGroup,
                "position" => (int)$fieldDefinitionCreateStruct->position,
                "fieldType" => $fieldDefinitionCreateStruct->fieldTypeIdentifier,
                "isTranslatable" => $fieldDefinitionCreateStruct->isTranslatable,
                "isRequired" => $fieldDefinitionCreateStruct->isRequired,
                "isInfoCollector" => $fieldDefinitionCreateStruct->isInfoCollector,
                "isSearchable" => $fieldDefinitionCreateStruct->isSearchable
                // These properties are precreated in constructor
                //"fieldTypeConstraints"
                //"defaultValue"
            )
        );
        /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
        $fieldType = $this->repository->getFieldTypeService()->buildFieldType(
            $fieldDefinitionCreateStruct->fieldTypeIdentifier
        );

        $validationErrors = $fieldType->validateValidatorConfiguration(
            $fieldDefinitionCreateStruct->validatorConfiguration
        );
        if ( !empty( $validationErrors ) )
        {
            throw new ContentTypeFieldDefinitionValidationException( $validationErrors );
        }

        $validationErrors = $fieldType->validateFieldSettings(
            $fieldDefinitionCreateStruct->fieldSettings
        );
        if ( !empty( $validationErrors ) )
        {
            throw new ContentTypeFieldDefinitionValidationException( $validationErrors );
        }

        $spiFieldDefinition->fieldTypeConstraints->validators = $fieldDefinitionCreateStruct->validatorConfiguration;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldDefinitionCreateStruct->fieldSettings;
        $spiFieldDefinition->defaultValue = $fieldType->toPersistenceValue(
            isset( $fieldDefinitionCreateStruct->defaultValue )
                ? $fieldType->acceptValue( $fieldDefinitionCreateStruct->defaultValue )
                : $fieldType->getEmptyValue()
        );

        return $spiFieldDefinition;
    }

    /**
     * Builds SPIFieldDefinition object using API FieldDefinitionUpdateStruct
     * and API FieldDefinition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException if validator configuration or
     *         field setting do not validate
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function buildSPIFieldDefinitionUpdate( FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct, APIFieldDefinition $fieldDefinition )
    {
        /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
        $fieldType = $this->repository->getFieldTypeService()->buildFieldType(
            $fieldDefinition->fieldTypeIdentifier
        );

        $validatorConfiguration = $fieldDefinitionUpdateStruct->validatorConfiguration === null
            ? $fieldDefinition->validatorConfiguration
            : $fieldDefinitionUpdateStruct->validatorConfiguration;
        $fieldSettings = $fieldDefinitionUpdateStruct->fieldSettings === null
            ? $fieldDefinition->fieldSettings
            : $fieldDefinitionUpdateStruct->fieldSettings;

        $validationErrors = $fieldType->validateValidatorConfiguration( $validatorConfiguration );
        if ( !empty( $validationErrors ) )
        {
            throw new ContentTypeFieldDefinitionValidationException( $validationErrors );
        }

        $validationErrors = $fieldType->validateFieldSettings( $fieldSettings );
        if ( !empty( $validationErrors ) )
        {
            throw new ContentTypeFieldDefinitionValidationException( $validationErrors );
        }

        $spiFieldDefinition = new SPIFieldDefinition(
            array(
                "id" => $fieldDefinition->id,
                "fieldType" => $fieldDefinition->fieldTypeIdentifier,
                "name" => $fieldDefinitionUpdateStruct->names === null
                    ? $fieldDefinition->getNames()
                    : $fieldDefinitionUpdateStruct->names,
                "description" => $fieldDefinitionUpdateStruct->descriptions === null
                    ? $fieldDefinition->getDescriptions()
                    : $fieldDefinitionUpdateStruct->descriptions,
                "identifier" => $fieldDefinitionUpdateStruct->identifier === null
                    ? $fieldDefinition->identifier
                    : $fieldDefinitionUpdateStruct->identifier,
                "fieldGroup" => $fieldDefinitionUpdateStruct->fieldGroup === null
                    ? $fieldDefinition->fieldGroup
                    : $fieldDefinitionUpdateStruct->fieldGroup,
                "position" => $fieldDefinitionUpdateStruct->position === null
                    ? $fieldDefinition->position
                    : $fieldDefinitionUpdateStruct->position,
                "isTranslatable" => $fieldDefinitionUpdateStruct->isTranslatable === null
                    ? $fieldDefinition->isTranslatable
                    : $fieldDefinitionUpdateStruct->isTranslatable,
                "isRequired" => $fieldDefinitionUpdateStruct->isRequired === null
                    ? $fieldDefinition->isRequired
                    : $fieldDefinitionUpdateStruct->isRequired,
                "isInfoCollector" => $fieldDefinitionUpdateStruct->isInfoCollector === null
                    ? $fieldDefinition->isInfoCollector
                    : $fieldDefinitionUpdateStruct->isInfoCollector,
                "isSearchable" => $fieldDefinitionUpdateStruct->isSearchable === null
                    ? $fieldDefinition->isSearchable
                    : $fieldDefinitionUpdateStruct->isSearchable,
                // These properties are precreated in constructor
                //"fieldTypeConstraints"
                //"defaultValue"
            )
        );

        $spiFieldDefinition->fieldTypeConstraints->validators = $validatorConfiguration;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldSettings;
        $spiFieldDefinition->defaultValue = $fieldType->toPersistenceValue(
            isset( $fieldDefinitionUpdateStruct->defaultValue )
                ? $fieldType->acceptValue( $fieldDefinitionUpdateStruct->defaultValue )
                : $fieldType->getEmptyValue()
        );

        return $spiFieldDefinition;
    }

    /**
     * Builds a ContentType domain object from value object returned by persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $spiContentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function buildContentTypeDomainObject( SPIContentType $spiContentType )
    {
        $mainLanguageCode = $this->repository->getContentLanguageService()->loadLanguageById(
            $spiContentType->initialLanguageId
        )->languageCode;

        $contentTypeGroups = array();
        foreach ( $spiContentType->groupIds as $groupId )
        {
            $contentTypeGroups[] = $this->loadContentTypeGroup( $groupId );
        }

        $fieldDefinitions = array();
        foreach ( $spiContentType->fieldDefinitions as $spiFieldDefinition )
        {
            $fieldDefinitions[] = $this->buildFieldDefinitionDomainObject( $spiFieldDefinition );
        }

        return new ContentType(
            array(
                "names" => $spiContentType->name,
                "descriptions" => $spiContentType->description,
                "contentTypeGroups" => $contentTypeGroups,
                "fieldDefinitions" => $fieldDefinitions,
                "id" => $spiContentType->id,
                "status" => $spiContentType->status,
                "identifier" => $spiContentType->identifier,
                "creationDate" => $this->getDateTime( $spiContentType->created ),
                "modificationDate" => $this->getDateTime( $spiContentType->modified ),
                "creatorId" => $spiContentType->creatorId,
                "modifierId" => $spiContentType->modifierId,
                "remoteId" => $spiContentType->remoteId,
                "urlAliasSchema" => $spiContentType->urlAliasSchema,
                "nameSchema" => $spiContentType->nameSchema,
                "isContainer" => $spiContentType->isContainer,
                "mainLanguageCode" => $mainLanguageCode,
                "defaultAlwaysAvailable" => $spiContentType->defaultAlwaysAvailable,
                "defaultSortField" => $spiContentType->sortField,
                "defaultSortOrder" => $spiContentType->sortOrder
            )
        );
    }

    /**
     * Builds a FieldDefinition domain object from value object returned by persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $spiFieldDefinition
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    protected function buildFieldDefinitionDomainObject( SPIFieldDefinition $spiFieldDefinition )
    {
        /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
        $fieldType = $this->repository->getFieldTypeService()->buildFieldType( $spiFieldDefinition->fieldType );
        $fieldDefinition = new FieldDefinition(
            array(
                "names" => $spiFieldDefinition->name,
                "descriptions" => $spiFieldDefinition->description,
                "id" => $spiFieldDefinition->id,
                "identifier" => $spiFieldDefinition->identifier,
                "fieldGroup" => $spiFieldDefinition->fieldGroup,
                "position" => $spiFieldDefinition->position,
                "fieldTypeIdentifier" => $spiFieldDefinition->fieldType,
                "isTranslatable" => $spiFieldDefinition->isTranslatable,
                "isRequired" => $spiFieldDefinition->isRequired,
                "isInfoCollector" => $spiFieldDefinition->isInfoCollector,
                "defaultValue" => $fieldType->fromPersistenceValue( $spiFieldDefinition->defaultValue ),
                "isSearchable" => $spiFieldDefinition->isSearchable,
                "fieldSettings" => (array)$spiFieldDefinition->fieldTypeConstraints->fieldSettings,
                "validatorConfiguration" => (array)$spiFieldDefinition->fieldTypeConstraints->validators,
            )
        );

        return $fieldDefinition;
    }

    /**
     * Builds a ContentTypeDraft domain object from value object returned by persistence
     * Decorates ContentType
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $spiContentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    protected function buildContentTypeDraftDomainObject( SPIContentType $spiContentType )
    {
        return new ContentTypeDraft(
            array(
                "innerContentType" => $this->buildContentTypeDomainObject( $spiContentType )
            )
        );
    }

    /**
     * Get a Content Type object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If a content type with the given id and status DEFINED can not be found
     *
     * @param int $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentType( $contentTypeId )
    {
        if ( !is_numeric( $contentTypeId ) )
        {
            throw new InvalidArgumentValue( '$contentTypeId', $contentTypeId );
        }

        $spiContentType = $this->contentTypeHandler->load(
            $contentTypeId,
            SPIContentType::STATUS_DEFINED
        );

        return $this->buildContentTypeDomainObject(
            $spiContentType
        );
    }

    /**
     * Get a Content Type object by identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given identifier and status DEFINED can not be found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier( $identifier )
    {
        if ( !is_string( $identifier ) )
        {
            throw new InvalidArgumentValue( '$identifier', $identifier );
        }

        $spiContentType = $this->contentTypeHandler->loadByIdentifier(
            $identifier
        );

        return $this->buildContentTypeDomainObject(
            $spiContentType
        );
    }

    /**
     * Get a Content Type object by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content type with the given remote id and status DEFINED can not be found
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId( $remoteId )
    {
        $spiContentType = $this->contentTypeHandler->loadByRemoteId( $remoteId );

        return $this->buildContentTypeDomainObject(
            $spiContentType
        );
    }

    /**
     * Get a Content Type object draft by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param int $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @todo Use another exception when user of draft is someone else
     */
    public function loadContentTypeDraft( $contentTypeId )
    {
        if ( !is_numeric( $contentTypeId ) )
        {
            throw new InvalidArgumentValue( '$contentTypeId', $contentTypeId );
        }

        $spiContentType = $this->contentTypeHandler->load(
            $contentTypeId,
            SPIContentType::STATUS_DRAFT
        );

        if ( $spiContentType->modifierId != $this->repository->getCurrentUser()->id )
        {
            throw new NotFoundException( "ContentType owned by someone else", $contentTypeId );
        }

        return $this->buildContentTypeDraftDomainObject(
            $spiContentType
        );
    }

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[] an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes( APIContentTypeGroup $contentTypeGroup )
    {
        $spiContentTypes = $this->contentTypeHandler->loadContentTypes(
            $contentTypeGroup->id,
            SPIContentType::STATUS_DEFINED
        );
        $contentTypes = array();

        foreach ( $spiContentTypes as $spiContentType )
        {
            $contentTypes[] = $this->buildContentTypeDomainObject( $spiContentType );
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
    public function createContentTypeDraft( APIContentType $contentType )
    {
        if ( $this->repository->hasAccess( 'class', 'create' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'create' );

        try
        {
            $this->contentTypeHandler->load(
                $contentType->id,
                SPIContentType::STATUS_DRAFT
            );

            throw new BadStateException(
                "\$contentType",
                "Draft of the ContentType already exists"
            );
        }
        catch ( APINotFoundException $e )
        {
            $this->repository->beginTransaction();
            try
            {
                $spiContentType = $this->contentTypeHandler->createDraft(
                    $this->repository->getCurrentUser()->id,
                    $contentType->id
                );
                $this->repository->commit();
            }
            catch ( Exception $e )
            {
                $this->repository->rollback();
                throw $e;
            }
        }

        return $this->buildContentTypeDraftDomainObject(
            $spiContentType
        );
    }

    /**
     * Update a Content Type object
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
    public function updateContentTypeDraft( APIContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
        {
            throw new UnauthorizedException( 'ContentType', 'update' );
        }

        try
        {
            $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );
        }
        catch ( APINotFoundException $e )
        {
            throw new InvalidArgumentException(
                "\$contentTypeDraft",
                "There is no ContentType draft assigned to the authenticated user",
                $e
            );
        }

        if ( $contentTypeUpdateStruct->identifier !== null
            && $contentTypeUpdateStruct->identifier != $loadedContentTypeDraft->identifier )
        {
            try
            {
                $this->loadContentTypeByIdentifier( $contentTypeUpdateStruct->identifier );

                throw new InvalidArgumentException(
                    "\$contentTypeUpdateStruct",
                    "Another ContentType with identifier '{$contentTypeUpdateStruct->identifier}' exists"
                );
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }

        if ( $contentTypeUpdateStruct->remoteId !== null
            && $contentTypeUpdateStruct->remoteId != $loadedContentTypeDraft->remoteId )
        {
            try
            {
                $this->loadContentTypeByRemoteId( $contentTypeUpdateStruct->remoteId );

                throw new InvalidArgumentException(
                    "\$contentTypeUpdateStruct",
                    "Another ContentType with remoteId '{$contentTypeUpdateStruct->remoteId}' exists"
                );
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->update(
                $contentTypeDraft->id,
                $contentTypeDraft->status,
                $this->buildSPIContentTypeUpdateStruct(
                    $loadedContentTypeDraft,
                    $contentTypeUpdateStruct
                )
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Builds ContentType update struct for storage layer
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct
     */
    protected function buildSPIContentTypeUpdateStruct( APIContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct )
    {
        $updateStruct = new SPIContentTypeUpdateStruct();

        $updateStruct->identifier = $contentTypeUpdateStruct->identifier !== null
            ? $contentTypeUpdateStruct->identifier
            : $contentTypeDraft->identifier;
        $updateStruct->remoteId = $contentTypeUpdateStruct->remoteId !== null
            ? $contentTypeUpdateStruct->remoteId
            : $contentTypeDraft->remoteId;

        $updateStruct->name = $contentTypeUpdateStruct->names !== null
            ? $contentTypeUpdateStruct->names
            : $contentTypeDraft->names;
        $updateStruct->description = $contentTypeUpdateStruct->descriptions !== null
            ? $contentTypeUpdateStruct->descriptions
            : $contentTypeDraft->descriptions;

        $updateStruct->modified = $contentTypeUpdateStruct->modificationDate !== null
            ? $contentTypeUpdateStruct->modificationDate->getTimestamp()
            : time();
        $updateStruct->modifierId = $contentTypeUpdateStruct->modifierId !== null
            ? $contentTypeUpdateStruct->modifierId
            : $this->repository->getCurrentUser()->id;

        $updateStruct->urlAliasSchema = $contentTypeUpdateStruct->urlAliasSchema !== null
            ? $contentTypeUpdateStruct->urlAliasSchema
            : $contentTypeDraft->urlAliasSchema;
        $updateStruct->nameSchema = $contentTypeUpdateStruct->nameSchema !== null
            ? $contentTypeUpdateStruct->nameSchema
            : $contentTypeDraft->nameSchema;

        $updateStruct->isContainer = $contentTypeUpdateStruct->isContainer !== null
            ? $contentTypeUpdateStruct->isContainer
            : $contentTypeDraft->isContainer;
        $updateStruct->sortField = $contentTypeUpdateStruct->defaultSortField !== null
            ? $contentTypeUpdateStruct->defaultSortField
            : $contentTypeDraft->defaultSortField;
        $updateStruct->sortOrder = $contentTypeUpdateStruct->defaultSortOrder !== null
            ? (int)$contentTypeUpdateStruct->defaultSortOrder
            : $contentTypeDraft->defaultSortOrder;

        $updateStruct->defaultAlwaysAvailable = $contentTypeUpdateStruct->defaultAlwaysAvailable !== null
            ? $contentTypeUpdateStruct->defaultAlwaysAvailable
            : $contentTypeDraft->defaultAlwaysAvailable;
        $updateStruct->initialLanguageId = $this->repository->getContentLanguageService()->loadLanguage(
            $contentTypeUpdateStruct->mainLanguageCode !== null
                ? $contentTypeUpdateStruct->mainLanguageCode
                : $contentTypeDraft->mainLanguageCode
        )->id;

        return $updateStruct;
    }

    /**
     * Delete a Content Type object.
     *
     * Deletes a content type if it has no instances
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there exist content objects of this type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     */
    public function deleteContentType( APIContentType $contentType )
    {
        if ( $this->repository->hasAccess( 'class', 'delete' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'delete' );

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->delete(
                $contentType->id,
                $contentType->status
            );
            $this->repository->commit();
        }
        catch ( APIBadStateException $e )
        {
            $this->repository->rollback();
            throw new BadStateException(
                "\$contentType",
                "Content still exists for the given ContentType",
                $e
            );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Copy Type incl fields and groupIds to a new Type object
     *
     * New Type will have $userId as creator / modifier, created / modified should be updated with current time,
     * updated remoteId and identifier should be appended with '_' + unique string.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to copy a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\User\User $user if null the current user is used
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function copyContentType( APIContentType $contentType, User $user = null )
    {
        if ( $this->repository->hasAccess( 'class', 'create' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'create' );

        if ( empty( $user ) )
        {
            $user = $this->repository->getCurrentUser();
        }

        $this->repository->beginTransaction();
        try
        {
            $spiContentType = $this->contentTypeHandler->copy(
                $user->id,
                $contentType->id,
                SPIContentType::STATUS_DEFINED
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadContentType( $spiContentType->id );
    }

    /**
     * assign a content type to a content type group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to unlink a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the content type is already assigned the given group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     */
    public function assignContentTypeGroup( APIContentType $contentType, APIContentTypeGroup $contentTypeGroup )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        $spiContentType = $this->contentTypeHandler->load(
            $contentType->id,
            $contentType->status
        );

        if ( in_array( $contentTypeGroup->id, $spiContentType->groupIds ) )
        {
            throw new InvalidArgumentException(
                "\$contentTypeGroup",
                "The given ContentType is already assigned to the ContentTypeGroup"
            );
        }

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->link(
                $contentTypeGroup->id,
                $contentType->id,
                $contentType->status
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
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
    public function unassignContentTypeGroup( APIContentType $contentType, APIContentTypeGroup $contentTypeGroup )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        $spiContentType = $this->contentTypeHandler->load(
            $contentType->id,
            $contentType->status
        );

        if ( !in_array( $contentTypeGroup->id, $spiContentType->groupIds ) )
        {
            throw new InvalidArgumentException(
                "\$contentTypeGroup",
                "The given ContentType is not assigned the ContentTypeGroup"
            );
        }

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->unlink(
                $contentTypeGroup->id,
                $contentType->id,
                $contentType->status
            );
            $this->repository->commit();
        }
        catch ( APIBadStateException $e )
        {
            $this->repository->rollback();
            throw new BadStateException(
                "\$contentType",
                "The given ContentTypeGroup is the last group assigned to the ContentType",
                $e
            );
        }
        catch ( Exception $e )
        {
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
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function addFieldDefinition( APIContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );

        if ( $loadedContentTypeDraft->getFieldDefinition( $fieldDefinitionCreateStruct->identifier ) !== null )
        {
            throw new InvalidArgumentException(
                "\$fieldDefinitionCreateStruct",
                "Another FieldDefinition with identifier '{$fieldDefinitionCreateStruct->identifier}' exists in the ContentType"
            );
        }

        $spiFieldDefinitionCreateStruct = $this->buildSPIFieldDefinitionCreate(
            $fieldDefinitionCreateStruct
        );

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->addFieldDefinition(
                $contentTypeDraft->id,
                $contentTypeDraft->status,
                $spiFieldDefinitionCreateStruct
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
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
    public function removeFieldDefinition( APIContentTypeDraft $contentTypeDraft, APIFieldDefinition $fieldDefinition )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        $loadedFieldDefinition = $this->loadContentTypeDraft(
            $contentTypeDraft->id
        )->getFieldDefinition(
            $fieldDefinition->identifier
        );

        if ( empty( $loadedFieldDefinition ) || $loadedFieldDefinition->id != $fieldDefinition->id )
        {
            throw new InvalidArgumentException(
                "\$fieldDefinition",
                "The given FieldDefinition does not belong to the ContentType"
            );
        }

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->removeFieldDefinition(
                $contentTypeDraft->id,
                SPIContentType::STATUS_DRAFT,
                $fieldDefinition->id
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Update a field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     *                                                                        If the given identifier is used in an existing field of the given content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition( APIContentTypeDraft $contentTypeDraft, APIFieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
            throw new UnauthorizedException( 'ContentType', 'update' );

        $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );
        $foundFieldId = false;
        foreach ( $loadedContentTypeDraft->fieldDefinitions as $existingFieldDefinition )
        {
            if ( $existingFieldDefinition->id == $fieldDefinition->id )
            {
                $foundFieldId = true;
            }
            else if ( $existingFieldDefinition->identifier == $fieldDefinitionUpdateStruct->identifier )
            {
                throw new InvalidArgumentException(
                    "\$fieldDefinitionUpdateStruct",
                    "Another FieldDefinition with identifier '{$fieldDefinitionUpdateStruct->identifier}' exists in the ContentType"
                );
            }
        }
        if ( !$foundFieldId )
        {
            throw new InvalidArgumentException(
                "\$fieldDefinition",
                "The given FieldDefinition does not belong to the ContentType"
            );
        }

        $spiFieldDefinitionUpdateStruct = $this->buildSPIFieldDefinitionUpdate(
            $fieldDefinitionUpdateStruct,
            $fieldDefinition
        );

        $this->repository->beginTransaction();
        try
        {
            $this->contentTypeHandler->updateFieldDefinition(
                $contentTypeDraft->id,
                SPIContentType::STATUS_DRAFT,
                $spiFieldDefinitionUpdateStruct
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
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
    public function publishContentTypeDraft( APIContentTypeDraft $contentTypeDraft )
    {
        if ( $this->repository->hasAccess( 'class', 'update' ) !== true )
        {
            throw new UnauthorizedException( 'ContentType', 'update' );
        }

        try
        {
            $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );
        }
        catch ( APINotFoundException $e )
        {
            throw new BadStateException(
                "\$contentTypeDraft",
                "The content type does not have a draft.",
                $e
            );
        }

        if ( count( $loadedContentTypeDraft->getFieldDefinitions() ) === 0 )
        {
            throw new InvalidArgumentException(
                "\$contentTypeDraft",
                "The content type draft should have at least one field definition."
            );
        }

        $this->repository->beginTransaction();
        try
        {
            if ( empty( $loadedContentTypeDraft->nameSchema ) )
            {
                $fieldDefinitions = $loadedContentTypeDraft->getFieldDefinitions();
                $this->contentTypeHandler->update(
                    $contentTypeDraft->id,
                    $contentTypeDraft->status,
                    $this->buildSPIContentTypeUpdateStruct(
                        $loadedContentTypeDraft,
                        new ContentTypeUpdateStruct(
                            array(
                                "nameSchema" => "<" . $fieldDefinitions[0]->identifier . ">"
                            )
                        )
                    )
                );
            }

            $this->contentTypeHandler->publish(
                $loadedContentTypeDraft->id
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * instantiates a new content type group create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct( $identifier )
    {
        if ( !is_string( $identifier ) )
        {
            throw new InvalidArgumentValue( '$identifier', $identifier );
        }

        return new ContentTypeGroupCreateStruct(
            array(
                "identifier" => $identifier
            )
        );
    }

    /**
     * instantiates a new content type create class
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct( $identifier )
    {
        if ( !is_string( $identifier ) )
        {
            throw new InvalidArgumentValue( '$identifier', $identifier );
        }

        return new ContentTypeCreateStruct(
            array(
                "identifier" => $identifier
            )
        );
    }

    /**
     * Instantiates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct()
    {
        return new ContentTypeUpdateStruct;
    }

    /**
     * instantiates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct()
    {
        return new ContentTypeGroupUpdateStruct;
    }

    /**
     * instantiates a field definition create struct
     *
     * @param string $fieldTypeIdentifier the required  field type identifier
     * @param string $identifier the required identifier for the field definition
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct( $identifier, $fieldTypeIdentifier )
    {
        if ( !is_string( $identifier ) )
        {
            throw new InvalidArgumentValue( '$identifier', $identifier );
        }

        if ( !is_string( $fieldTypeIdentifier ) )
        {
            throw new InvalidArgumentValue( '$fieldTypeIdentifier', $fieldTypeIdentifier );
        }

        return new FieldDefinitionCreateStruct(
            array(
                "identifier" => $identifier,
                "fieldTypeIdentifier" => $fieldTypeIdentifier
            )
        );
    }

    /**
     * instantiates a field definition update class
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct()
    {
        return new FieldDefinitionUpdateStruct;
    }
}
