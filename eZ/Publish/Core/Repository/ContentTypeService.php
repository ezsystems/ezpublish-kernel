<?php
/**
 * @package eZ\Publish\Core\Repository
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,
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
    DateTime;

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
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->settings = $settings;
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
        try
        {
            $this->loadContentTypeGroupByIdentifier( $contentTypeGroupCreateStruct->identifier );

            throw new InvalidArgumentException(
                '$contentTypeGroupCreateStruct->identifier',
                "a group with the same identifier already exists"
            );
        }
        catch ( APINotFoundException $e ) {}

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
                "name"        => $contentTypeGroupCreateStruct->names,
                "description" => $contentTypeGroupCreateStruct->descriptions,
                "identifier"  => $contentTypeGroupCreateStruct->identifier,
                "created"     => $timestamp,
                "modified"    => $timestamp,
                "creatorId"   => $userId,
                "modifierId"  => $userId
            )
        );

        return $this->buildContentTypeGroupDomainObject(
            $this->persistenceHandler->contentTypeHandler()->createGroup(
                $spiGroupCreateStruct
            )
        );
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

        $spiGroup = $this->persistenceHandler->contentTypeHandler()->loadGroup(
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
        $spiGroups = $this->persistenceHandler->contentTypeHandler()->loadAllGroups();

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
        try
        {
            $this->loadContentTypeGroupByIdentifier( $contentTypeGroupUpdateStruct->identifier );

            throw new InvalidArgumentException(
                '$contentTypeGroupUpdateStruct->identifier',
                "given identifier already exists"
            );
        }
        catch ( APINotFoundException $e ) {}

        if ( $contentTypeGroupUpdateStruct->modificationDate !== null )
            $modifiedTimestamp = $contentTypeGroupUpdateStruct->modificationDate->getTimestamp();
        else
            $modifiedTimestamp = time();

        $spiGroupUpdateStruct = new SPIContentTypeGroupUpdateStruct(
            array(
                "id"          => $contentTypeGroup->id,
                "name"        => $contentTypeGroupUpdateStruct->names,
                "description" => $contentTypeGroupUpdateStruct->descriptions,
                "identifier"  => $contentTypeGroupUpdateStruct->identifier,
                "modified"    => $modifiedTimestamp,
                "modifierId"  => $contentTypeGroupUpdateStruct->modifierId
            )
        );

        $this->persistenceHandler->contentTypeHandler()->updateGroup(
            $spiGroupUpdateStruct
        );
    }

    /**
     * Delete a Content Type Group.
     *
     * This method only deletes an content type group which has content types without any content instances
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If  a to be deleted content type has instances
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function deleteContentTypeGroup( APIContentTypeGroup $contentTypeGroup )
    {
        try
        {
            $this->persistenceHandler->contentTypeHandler()->deleteGroup(
                $contentTypeGroup->id
            );
        }
        catch ( APIBadStateException $e )
        {
            throw new InvalidArgumentException(
                "contentTypeGroup",
                "content class group has content type instances",
                $e
            );
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
        $modificationDate = new DateTime();
        $modificationDate->setTimestamp( $spiGroup->modified );
        $creationDate = new DateTime();
        $creationDate->setTimestamp( $spiGroup->created );

        return new ContentTypeGroup(
            array(
                "id"               => $spiGroup->id,
                "identifier"       => $spiGroup->identifier,
                "creationDate"     => $creationDate,
                "modificationDate" => $modificationDate,
                "creatorId"        => $spiGroup->creatorId,
                "modifierId"       => $spiGroup->modifierId,
                "names"            => $spiGroup->name,
                "descriptions"     => $spiGroup->description
            )
        );
    }

    /**
     * Create a Content Type object.
     *
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the identifier or remoteId in the content type create struct already exists
     *         or there is a duplicate field identifier
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param array $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType( APIContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups )
    {
        if ( count( $contentTypeGroups ) === 0 )
        {
            throw new InvalidArgumentException(
                '$contentTypeGroups',
                "array must contain at least one contentTypeGroup"
            );
        }

        try
        {
            $this->persistenceHandler->contentTypeHandler()->loadByIdentifier(
                $contentTypeCreateStruct->identifier
            );

            throw new InvalidArgumentException(
                '$contentTypeCreateStruct->identifier',
                "identifier in the content type create struct already exists"
            );
        }
        catch ( APINotFoundException $e ) {}

        try
        {
            $this->persistenceHandler->contentTypeHandler()->loadByRemoteId(
                $contentTypeCreateStruct->remoteId
            );

            throw new InvalidArgumentException(
                '$contentTypeCreateStruct->remoteId',
                "remoteId in the content type create struct already exists"
            );
        }
        catch ( APINotFoundException $e ) {}

        if ( $contentTypeCreateStruct->creatorId === null )
            $userId = $this->repository->getCurrentUser()->id;
        else
            $userId = $contentTypeCreateStruct->creatorId;

        if ( $contentTypeCreateStruct->creationDate === null )
            $timestamp = time();
        else
            $timestamp = $contentTypeCreateStruct->creationDate->getTimestamp();

        $initialLanguageId = $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
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
                "name"                   => $contentTypeCreateStruct->names,
                "status"                 => APIContentType::STATUS_DRAFT,
                "description"            => $contentTypeCreateStruct->descriptions,
                "identifier"             => $contentTypeCreateStruct->identifier,
                "created"                => $timestamp,
                "modified"               => $timestamp,
                "creatorId"              => $userId,
                "modifierId"             => $userId,
                "remoteId"               => $contentTypeCreateStruct->remoteId,
                "urlAliasSchema"         => $contentTypeCreateStruct->urlAliasSchema,
                "nameSchema"             => $contentTypeCreateStruct->nameSchema,
                "isContainer"            => $contentTypeCreateStruct->isContainer,
                "initialLanguageId"      => $initialLanguageId,
                "sortField"              => $contentTypeCreateStruct->defaultSortField,
                "sortOrder"              => $contentTypeCreateStruct->defaultSortOrder,
                "groupIds"               => $groupIds,
                "fieldDefinitions"       => $fieldDefinitions,
                "defaultAlwaysAvailable" => $contentTypeCreateStruct->defaultAlwaysAvailable
            )
        );

        return $this->buildContentTypeDraftDomainObject(
            $this->persistenceHandler->contentTypeHandler()->create(
                $spiContentTypeCreateStruct
            )
        );
    }

    protected function buildSPIFieldDefinitionCreate( FieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        $spiFieldDefinition = new SPIFieldDefinition(
            array(
                "id"                   => null,
                "name"                 => $fieldDefinitionCreateStruct->names,
                "description"          => $fieldDefinitionCreateStruct->descriptions,
                "identifier"           => $fieldDefinitionCreateStruct->identifier,
                "fieldGroup"           => $fieldDefinitionCreateStruct->fieldGroup,
                "position"             => $fieldDefinitionCreateStruct->position,
                "fieldType"            => $fieldDefinitionCreateStruct->fieldTypeIdentifier,
                "isTranslatable"       => $fieldDefinitionCreateStruct->isTranslatable,
                "isRequired"           => $fieldDefinitionCreateStruct->isRequired,
                "isInfoCollector"      => $fieldDefinitionCreateStruct->isInfoCollector,
                //"fieldTypeConstraints"
                "defaultValue"         => $fieldDefinitionCreateStruct->defaultValue,
                "isSearchable"         => $fieldDefinitionCreateStruct->isSearchable
            )
        );
        $spiFieldDefinition->fieldTypeConstraints->validators = $fieldDefinitionCreateStruct->validators;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldDefinitionCreateStruct->fieldSettings;

        return $spiFieldDefinition;
    }

    protected function buildSPIFieldDefinitionUpdate( FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct, APIFieldDefinition $fieldDefinition )
    {
        $spiFieldDefinition = new SPIFieldDefinition(
            array(
                "id"              => $fieldDefinition->id,
                "name"            => $fieldDefinitionUpdateStruct->names,
                "description"     => $fieldDefinitionUpdateStruct->descriptions,
                "identifier"      => $fieldDefinitionUpdateStruct->identifier,
                "fieldGroup"      => $fieldDefinitionUpdateStruct->fieldGroup,
                "position"        => $fieldDefinitionUpdateStruct->position,
                "fieldType"       => $fieldDefinition->fieldTypeIdentifier,
                "isTranslatable"  => $fieldDefinitionUpdateStruct->isTranslatable,
                "isRequired"      => $fieldDefinitionUpdateStruct->isRequired,
                "isInfoCollector" => $fieldDefinitionUpdateStruct->isInfoCollector,
                //"fieldTypeConstraints"
                "defaultValue"    => $fieldDefinitionUpdateStruct->defaultValue,
                "isSearchable"    => $fieldDefinitionUpdateStruct->isSearchable
            )
        );
        $spiFieldDefinition->fieldTypeConstraints->validators = $fieldDefinitionUpdateStruct->validators;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldDefinitionUpdateStruct->fieldSettings;

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
        $modifiedDate = new DateTime;
        $modifiedDate->setTimestamp( $spiContentType->modified );
        $createdDate = new DateTime();
        $createdDate->setTimestamp( $spiContentType->created );

        $mainLanguageCode = $this->persistenceHandler->contentLanguageHandler()->load(
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
                "names"                  => $spiContentType->name,
                "descriptions"           => $spiContentType->description,
                "contentTypeGroups"      => $contentTypeGroups,
                "fieldDefinitions"       => $fieldDefinitions,
                "id"                     => $spiContentType->id,
                "status"                 => $spiContentType->status,
                "identifier"             => $spiContentType->identifier,
                "creationDate"           => $createdDate,
                "modificationDate"       => $modifiedDate,
                "creatorId"              => $spiContentType->creatorId,
                "modifierId"             => $spiContentType->modifierId,
                "remoteId"               => $spiContentType->remoteId,
                "urlAliasSchema"         => $spiContentType->urlAliasSchema,
                "nameSchema"             => $spiContentType->nameSchema,
                "isContainer"            => $spiContentType->isContainer,
                "mainLanguageCode"       => $mainLanguageCode,
                "defaultAlwaysAvailable" => $spiContentType->defaultAlwaysAvailable,
                "defaultSortField"       => $spiContentType->sortField,
                "defaultSortOrder"       => $spiContentType->sortOrder
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
        return new FieldDefinition(
            array(
                "names"                     => $spiFieldDefinition->name,
                "descriptions"              => $spiFieldDefinition->description,
                "fieldSettings"             => $spiFieldDefinition->fieldTypeConstraints->fieldSettings,
                "validators"                => $spiFieldDefinition->fieldTypeConstraints->validators,
                "id"                        => $spiFieldDefinition->id,
                "identifier"                => $spiFieldDefinition->identifier,
                "fieldGroup"                => $spiFieldDefinition->fieldGroup,
                "position"                  => $spiFieldDefinition->position,
                "fieldTypeIdentifier"       => $spiFieldDefinition->fieldType,
                "isTranslatable"            => $spiFieldDefinition->isTranslatable,
                "isRequired"                => $spiFieldDefinition->isRequired,
                "isInfoCollector"           => $spiFieldDefinition->isInfoCollector,
                "defaultValue"              => $spiFieldDefinition->defaultValue,
                "isSearchable"              => $spiFieldDefinition->isSearchable
            )
        );
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

        /** @var $spiContentType \eZ\Publish\SPI\Persistence\Content\Type */
        $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
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

        /** @var $spiContentType \eZ\Publish\SPI\Persistence\Content\Type */
        $spiContentType = $this->persistenceHandler->contentTypeHandler()->loadByIdentifier(
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
        $spiContentType = $this->persistenceHandler->contentTypeHandler()->loadByRemoteId( $remoteId );

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
     * @todo enable user check
     */
    public function loadContentTypeDraft( $contentTypeId )
    {
        if ( !is_numeric( $contentTypeId ) )
        {
            throw new InvalidArgumentValue( '$contentTypeId', $contentTypeId );
        }

        $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
            $contentTypeId,
            SPIContentType::STATUS_DRAFT
        );

        /*
        $currentUser = $this->repository->getCurrentUser();

        if ( $spiContentType->modifierId !== $currentUser->id )
        {
            throw new NotFoundException( "contentType", $contentTypeId );
        }
        */

        return $this->buildContentTypeDraftDomainObject(
            $spiContentType
        );
    }

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     *
     * @return array an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes( APIContentTypeGroup $contentTypeGroup )
    {
        $spiContentTypes = $this->persistenceHandler->contentTypeHandler()->loadContentTypes(
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
        try
        {
            $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
                $contentType->id,
                SPIContentType::STATUS_DRAFT
            );
            $currentUser = $this->repository->getCurrentUser();

            if ( $spiContentType->modifierId !== $currentUser->id )
            {
                throw new BadStateException( "contentType", 'modifier of draft is not the same user as current user' );
            }
        }
        catch ( APINotFoundException $e )
        {
            $spiContentType = $this->persistenceHandler->contentTypeHandler()->createDraft(
                $contentType->modifierId,
                $contentType->id
            );
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
        try
        {
            $this->loadContentTypeDraft( $contentTypeDraft->id );
        }
        catch ( APINotFoundException $e )
        {
            throw new InvalidArgumentException(
                '$contentTypeUpdateStruct->identifier',
                "there is no draft assigned to the authenticated user",
                $e
            );
        }

        try
        {
            $this->loadContentTypeByIdentifier( $contentTypeUpdateStruct->identifier );

            throw new InvalidArgumentException(
                '$contentTypeUpdateStruct->identifier',
                "the given identifier already exists"
            );
        }
        catch ( APINotFoundException $e ) {}

        try
        {
            $this->loadContentTypeByRemoteId( $contentTypeUpdateStruct->remoteId );

            throw new InvalidArgumentException(
                '$contentTypeUpdateStruct->remoteId',
                "the given remoteId already exists"
            );
        }
        catch ( APINotFoundException $e ) {}

        $initialLanguageId = $this->persistenceHandler
            ->contentLanguageHandler()
                ->loadByLanguageCode(
                    $contentTypeUpdateStruct->mainLanguageCode
                )->id;

        if ( $contentTypeUpdateStruct->modifierId === null )
            $userId = $this->repository->getCurrentUser()->id;
        else
            $userId = $contentTypeUpdateStruct->modifierId;

        if ( $contentTypeUpdateStruct->modificationDate === null )
            $timestamp = time();
        else
            $timestamp = $contentTypeUpdateStruct->modificationDate->getTimestamp();

        $spiContentTypeUpdateStruct = new SPIContentTypeUpdateStruct(
            array(
                "name"                   => $contentTypeUpdateStruct->names,
                "description"            => $contentTypeUpdateStruct->descriptions,
                "identifier"             => $contentTypeUpdateStruct->identifier,
                "modified"               => $timestamp,
                "modifierId"             => $userId,
                "remoteId"               => $contentTypeUpdateStruct->remoteId,
                "urlAliasSchema"         => $contentTypeUpdateStruct->urlAliasSchema,
                "nameSchema"             => $contentTypeUpdateStruct->nameSchema,
                "isContainer"            => $contentTypeUpdateStruct->isContainer,
                "initialLanguageId"      => $initialLanguageId,
                "sortField"              => $contentTypeUpdateStruct->defaultSortField,
                "sortOrder"              => $contentTypeUpdateStruct->defaultSortOrder,
                "defaultAlwaysAvailable" => $contentTypeUpdateStruct->defaultAlwaysAvailable
            )
        );

        $this->persistenceHandler->contentTypeHandler()->update(
            $contentTypeDraft->id,
            $contentTypeDraft->status,
            $spiContentTypeUpdateStruct
        );
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
        try
        {
            $this->persistenceHandler->contentTypeHandler()->delete(
                $contentType->id,
                $contentType->status
            );
        }
        catch ( APIBadStateException $e )
        {
            throw new BadStateException( '$contentType', 'existing content exists of this content type', $e );
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
        if ( $user === null )
        {
            $user = $this->repository->getCurrentUser();
        }

        $spiContentType = $this->persistenceHandler->contentTypeHandler()->copy(
            $user->id,
            $contentType->id,
            SPIContentType::STATUS_DEFINED
        );

        return $this->buildContentTypeDomainObject(
            $spiContentType
        );
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
        $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
            $contentType->id,
            $contentType->status
        );

        if ( in_array( $contentTypeGroup->id, $spiContentType->groupIds ) )
        {
            throw new InvalidArgumentException(
                '$contentType',
                "the content type is already assigned to the given group"
            );
        }

        $this->persistenceHandler->contentTypeHandler()->link(
            $contentTypeGroup->id,
            $contentType->id,
            $contentType->status
        );
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
        $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
            $contentType->id,
            $contentType->status
        );

        if ( !in_array( $contentTypeGroup->id, $spiContentType->groupIds ) )
        {
            throw new InvalidArgumentException(
                '$contentType',
                "the content type is not assigned the given group"
            );
        }

        try
        {
            $this->persistenceHandler->contentTypeHandler()->unlink(
                $contentTypeGroup->id,
                $contentType->id,
                $contentType->status
            );
        }
        catch ( APIBadStateException $e )
        {
            throw new BadStateException( '$contentType', 'the type group is the last group assigned to this type', $e );
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
        $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );

        if ( $loadedContentTypeDraft->getFieldDefinition( $fieldDefinitionCreateStruct->identifier ) !== null )
        {
            throw new InvalidArgumentException(
                '$contentTypeDraft',
                "the identifier already exists in the content type"
            );
        }

        $this->persistenceHandler->contentTypeHandler()->addFieldDefinition(
            $contentTypeDraft->id,
            $contentTypeDraft->status,
            $this->buildSPIFieldDefinitionCreate(
                $fieldDefinitionCreateStruct
            )
        );
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
        $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );

        if ( $loadedContentTypeDraft->getFieldDefinition( $fieldDefinition->identifier ) === null )
        {
            throw new InvalidArgumentException(
                '$contentTypeDraft',
                "the given field definition does not belong to the given type"
            );
        }

        $this->persistenceHandler->contentTypeHandler()->removeFieldDefinition(
            $contentTypeDraft->id,
            SPIContentType::STATUS_DRAFT,
            $fieldDefinition->id
        );
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
                    '$fieldDefinitionUpdateStruct->identifier',
                    "the identifier already exists in another field of the content type"
                );
            }
        }
        if ( !$foundFieldId )
        {
            throw new InvalidArgumentException(
                '$fieldDefinition->id',
                "the given field does not belong to the content type"
            );
        }

        $this->persistenceHandler->contentTypeHandler()->updateFieldDefinition(
            $contentTypeDraft->id,
            SPIContentType::STATUS_DRAFT,
            $this->buildSPIFieldDefinitionUpdate(
                $fieldDefinitionUpdateStruct,
                $fieldDefinition
            )
        );
    }

    /**
     * Publish the content type and update content objects.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If the content type has no draft
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish a content type
     *
     * This method updates content objects, depending on the changed field definitions.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     */
    public function publishContentTypeDraft( APIContentTypeDraft $contentTypeDraft )
    {
        try
        {
            $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );
        }
        catch ( APINotFoundException $e )
        {
            throw new BadStateException( '$contentTypeDraft', 'this content type has no drafts to publish', $e );
        }

        $this->persistenceHandler->contentTypeHandler()->publish(
            $loadedContentTypeDraft->id
        );
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
     * instantiates a new content type update struct
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
                "identifier"          => $identifier,
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
