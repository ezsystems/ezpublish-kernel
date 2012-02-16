<?php
/**
 * @package eZ\Publish\Core\Repository
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Values\User\User,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct as APIFieldDefinitionCreateStruct,
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
    eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Type as SPIContentType,
    eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as SPIContentTypeCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct as SPIContentTypeUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as SPIFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as SPIContentTypeGroupCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as SPIContentTypeGroupUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints as SPIFieldTypeConstraints,
    ezp\Base\Exception\NotFound as BaseNotFound,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\IllegalArgumentException,
    eZ\Publish\Core\Base\Exceptions\BadStateException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Persistence\Legacy\Exception\GroupNotEmpty,
    eZ\Publish\Core\Persistence\Legacy\Exception\TypeNotFound,
    eZ\Publish\Core\Persistence\Legacy\Exception\TypeStillHasContent,
    eZ\Publish\Core\Persistence\Legacy\Exception\RemoveLastGroupFromType,
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
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
    }

    /**
     * Create a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If a group with the same identifier already exists
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

            throw new IllegalArgumentException(
                '$contentTypeGroupCreateStruct',
                "a group with the same identifier already exists"
            );
        }
        catch ( NotFoundException $e ) {}

        if ( $contentTypeGroupCreateStruct->creationDate === null )
        {
            $timestamp = time();
        }
        else
        {
            $timestamp = $contentTypeGroupCreateStruct->creationDate->getTimestamp();
        }

        $spiGroupCreateStruct = new SPIContentTypeGroupCreateStruct(
            array(
                "name" => $contentTypeGroupCreateStruct->names,
                "description" => $contentTypeGroupCreateStruct->descriptions,
                "identifier" => $contentTypeGroupCreateStruct->identifier,
                "created" => $timestamp,
                "modified" => $timestamp,
                "creatorId" => $contentTypeGroupCreateStruct->creatorId,
                "modifierId" => $contentTypeGroupCreateStruct->creatorId
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

        if ( $spiGroup === null )
        {
            throw new NotFoundException( 'ContentTypeGroup', $contentTypeGroupId );
        }

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
        // @todo is group identifier 5.x feature? db does not support this yet (PS)
    }

    /**
     * Get all Content Type Groups
     *
     * @return array an array of {@link ContentTypeGroup}
     */
    public function loadContentTypeGroups()
    {
        $spiGroups = $this->persistenceHandler->contentTypeHandler()->loadAllGroups();

        return array_map(
            function( $spiGroup )
            {
                return $this->buildContentTypeGroupDomainObject( $spiGroup );
            },
            $spiGroups
        );
    }

    /**
     * Update a Content Type Group object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create a content type group
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given identifier (if set) already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup the content type group to be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
     */
    public function updateContentTypeGroup( APIContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct )
    {
        // @todo db does not support this yet (5.x)
        if ( false )
        {
            throw new IllegalArgumentException(
                '$contentTypeGroupUpdateStruct->identifier',
                "given identifier already exists"
            );
        }

        if ( $contentTypeGroupUpdateStruct->modificationDate !== null )
            $modifiedTimestamp = $contentTypeGroupUpdateStruct->modificationDate->getTimestamp();
        else
            $modifiedTimestamp = time();

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
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If  a to be deleted content type has instances
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
        catch ( GroupNotEmpty $e )
        {
            throw new IllegalArgumentException(
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
    protected function buildContentTypeGroupDomainObject( $spiGroup )
    {
        $modificationDate = new DateTime( "@{$spiGroup->modified}" );
        $creationDate = new DateTime( "@{$spiGroup->created}" );

        return new ContentTypeGroup(
            array(
                "id" => $spiGroup->id,
                "identifier" => $spiGroup->identifier,
                "creationDate" => $creationDate,
                "modificationDate" => $modificationDate,
                "creatorId" => $creationDate,
                "modifierId" => $spiGroup->modifierId
            )
        );
    }

    /**
     * Create a Content Type object.
     *
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the identifier or remoteId in the content type create struct already exists
     *         or there is a duplicate field identifier
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param array $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     * @todo validating remoteid requires loadByRemoteId in persistence handler (5.x)
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

            throw new IllegalArgumentException(
                "contentTypeCreateStruct->identifier",
                "identifier in the content type create struct already exists"
            );
        }
        catch ( TypeNotFound $e ) {}

        if ( $contentTypeCreateStruct->creatorId === null )
            $userId = $this->repository->getCurrentUser()->id;
        else
            $userId = $contentTypeCreateStruct->creatorId;

        if ( $contentTypeCreateStruct->creationDate === null )
            $timestamp = $contentTypeCreateStruct->creationDate->getTimestamp();
        else
            $timestamp = time();

        $initialLanguageId = $this->persistenceHandler->contentLanguageHandler()->load(
            $contentTypeCreateStruct->mainLanguageCode
        );
        $groupIds = array_map(
            function( $contentTypeGroup )
            {
                return $contentTypeGroup->id;
            },
            $contentTypeGroups
        );

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
                "fieldDefinitions" => $contentTypeCreateStruct->fieldDefinitions,
                "defaultAlwaysAvailable" => $contentTypeCreateStruct->defaultAlwaysAvailable
            )
        );

        $spiContentType = $this->persistenceHandler->contentTypeHandler()->create(
            $spiContentTypeCreateStruct
        );

        return $this->buildContentTypeDraftDomainObject(
            $spiContentType
        );
    }

    /**
     * Builds a ContentType domain object from value object returned by persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $spiContentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function buildContentTypeDomainObject( $spiContentType )
    {
        $modifiedDate = new DateTime( "@{$spiContentType->modified}" );
        $createdDate = new DateTime( "@{$spiContentType->created}" );
        $mainLanguageCode = $this->persistenceHandler->contentLanguageHandler()->load(
            $spiContentType->initialLanguageId
        );
        $contentTypeGroups = array_map(
            function( $groupId )
            {
                return $this->loadContentTypeGroup( $groupId );
            },
            $spiContentType->groupIds
        );

        return new ContentType(
            array(
                "names" => $spiContentType->name,
                "descriptions" => $spiContentType->description,
                "contentTypeGroups" => $contentTypeGroups,
                "fieldDefinitions" => $spiContentType->fieldDefinitions,
                "id" => $spiContentType->id,
                "status" => $spiContentType->status,
                "identifier" => $spiContentType->identifier,
                "creationDate" => $createdDate,
                "modificationDate" => $modifiedDate,
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
     * Builds a ContentTypeDraft domain object from value object returned by persistence
     * Decorates ContentType
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $spiContentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    protected function buildContentTypeDraftDomainObject( $spiContentType )
    {
        return new ContentTypeDraft(
            array(
                "contentType" => $this->buildContentTypeDomainObject( $spiContentType )
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

        try
        {
            /** @var $spiContentType \eZ\Publish\SPI\Persistence\Content\Type */
            $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
                $contentTypeId,
                SPIContentType::STATUS_DEFINED
            );
        }
        catch ( BaseNotFound $e )
        {
            throw new NotFoundException(
                "contentType",
                $contentTypeId,
                $e
            );
        }

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

        try
        {
            /** @var $spiContentType \eZ\Publish\SPI\Persistence\Content\Type */
            $spiContentType = $this->persistenceHandler->contentTypeHandler()->loadByIdentifier(
                $identifier
            );
        }
        catch ( BaseNotFound $e )
        {
            throw new NotFoundException(
                "contentType",
                $identifier,
                $e
            );
        }

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
        // @todo needs loadByRemoteId in persistence handler
    }

    /**
     * Get a Content Type object draft by id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param int $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function loadContentTypeDraft( $contentTypeId )
    {
        if ( !is_numeric( $contentTypeId ) )
        {
            throw new InvalidArgumentValue( '$contentTypeId', $contentTypeId );
        }

        $spiContentType = null;

        try
        {
            /** @var $spiContentType \eZ\Publish\SPI\Persistence\Content\Type */
            $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
                $contentTypeId,
                SPIContentType::STATUS_DRAFT
            );
        }
        catch ( BaseNotFound $e )
        {
            throw new NotFoundException(
                "contentType",
                $contentTypeId,
                $e
            );
        }

        $currentUser = $this->repository->getCurrentUser();

        if ( $spiContentType->modifierId !== $currentUser->id )
        {
            throw new NotFoundException( "contentType", $contentTypeId );
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
     * @return array an array of {@link ContentType} which have status DEFINED
     */
    public function loadContentTypes( APIContentTypeGroup $contentTypeGroup )
    {
        $spiContentTypes = $this->persistenceHandler->contentTypeHandler()->loadContentTypes(
            $contentTypeGroup->id,
            SPIContentType::STATUS_DEFINED
        );

        return array_map(
            function( $spiContentType )
            {
                $this->buildContentTypeDomainObject( $spiContentType );
            },
            $spiContentTypes
        );
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
        $spiContentType = null;

        try
        {
            /** @var $spiContentType \eZ\Publish\SPI\Persistence\Content\Type */
            $spiContentType = $this->persistenceHandler->contentTypeHandler()->load(
                $contentType->id,
                SPIContentType::STATUS_DRAFT
            );
            $currentUser = $this->repository->getCurrentUser();

            if ( $spiContentType->modifierId !== $currentUser->id )
            {
                throw new BadStateException( "contentType" );
            }
        }
        catch ( BaseNotFound $e ) {}

        if ( $spiContentType === null )
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
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given identifier or remoteId already exists
     *         or there is no draft assigned to the authenticated user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     * @todo exceptions
     */
    public function updateContentTypeDraft( APIContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct )
    {
        $initialLanguageId = $this->persistenceHandler->contentLanguageHandler()->load(
            $contentTypeUpdateStruct->mainLanguageCode
        );

        if ( $contentTypeUpdateStruct->modifierId === null )
            $userId = $this->repository->getCurrentUser()->id;
        else
            $userId = $contentTypeUpdateStruct->modifierId;

        if ( $contentTypeUpdateStruct->modificationDate === null )
            $timestamp = $contentTypeUpdateStruct->modificationDate->getTimestamp();
        else
            $timestamp = time();

        $spiContentTypeUpdateStruct = new SPIContentTypeUpdateStruct(
            array(
                "name" => $contentTypeUpdateStruct->names,
                "description" => $contentTypeUpdateStruct->descriptions,
                "identifier" => $contentTypeUpdateStruct->identifier,
                "modified" => $timestamp,
                "modifierId" => $userId,
                "remoteId" => $contentTypeUpdateStruct->remoteId,
                "urlAliasSchema" => $contentTypeUpdateStruct->urlAliasSchema,
                "nameSchema" => $contentTypeUpdateStruct->nameSchema,
                "isContainer" => $contentTypeUpdateStruct->isContainer,
                "initialLanguageId" => $initialLanguageId,
                "sortField" => $contentTypeUpdateStruct->defaultSortField,
                "sortOrder" => $contentTypeUpdateStruct->defaultSortOrder,
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
        catch ( TypeStillHasContent $e )
        {
            throw new BadStateException( '$contentType', $e );
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
     * @todo updating identifier and remoteid not performed in persistence
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
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the content type is already assigned the given group
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
            throw new IllegalArgumentException(
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
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the content type is not assigned this the given group.
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
            throw new IllegalArgumentException(
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
        catch ( RemoveLastGroupFromType $e )
        {
            throw new BadStateException( '$contentType', $e );
        }
    }

    /**
     * Adds a new field definition to an existing content type.
     *
     * The content type must be in state DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the identifier in already exists in the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function addFieldDefinition( APIContentTypeDraft $contentTypeDraft, APIFieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );

        if ( $loadedContentTypeDraft->getFieldDefinition( $fieldDefinitionCreateStruct->identifier ) !== null )
        {
            throw new IllegalArgumentException(
                '$contentTypeDraft',
                "the identifier already exists in the content type"
            );
        }

        $fieldTypeConstraints = new SPIFieldTypeConstraints(
            array(
                "validators" => $fieldDefinitionCreateStruct->validators,
                "fieldSettings" => $fieldDefinitionCreateStruct->fieldSettings
            )
        );
        $spiFieldDefinition = new SPIFieldDefinition(
            array(
                "id" => null,
                "name" => $fieldDefinitionCreateStruct->names,
                "description" => $fieldDefinitionCreateStruct->descriptions,
                "identifier" => $fieldDefinitionCreateStruct->identifier,
                "fieldGroup" => $fieldDefinitionCreateStruct->fieldGroup,
                "position" => $fieldDefinitionCreateStruct->position,
                "fieldType" => $fieldDefinitionCreateStruct->fieldTypeIdentifier,
                "isTranslatable" => $fieldDefinitionCreateStruct->isTranslatable,
                "isRequired" => $fieldDefinitionCreateStruct->isRequired,
                "isInfoCollector" => $fieldDefinitionCreateStruct->isInfoCollector,
                "fieldTypeConstraints" => $fieldTypeConstraints,
                "defaultValue" => $fieldDefinitionCreateStruct->defaultValue,
                "isSearchable" => $fieldDefinitionCreateStruct->isSearchable
            )
        );

        $this->persistenceHandler->contentTypeHandler()->addFieldDefinition(
            $contentTypeDraft->id,
            $contentTypeDraft->status,
            $spiFieldDefinition
        );
    }

    /**
     * Remove a field definition from an existing Type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException If the given field definition does not belong to the given type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     */
    public function removeFieldDefinition( APIContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition )
    {
        $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );

        if ( $loadedContentTypeDraft->getFieldDefinition( $fieldDefinition->identifier ) === null )
        {
            throw new IllegalArgumentException(
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException  If the given identifier is used in an existing field of the given content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition( APIContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct )
    {
        $loadedContentTypeDraft = $this->loadContentTypeDraft( $contentTypeDraft->id );

        if ( $loadedContentTypeDraft->getFieldDefinition( $fieldDefinition->identifier ) !== null )
        {
            throw new IllegalArgumentException(
                '$contentTypeDraft',
                "the identifier already exists in another field of the content type"
            );
        }

        $fieldTypeConstraints = new SPIFieldTypeConstraints(
            array(
                "validators" => $fieldDefinitionUpdateStruct->validators,
                "fieldSettings" => $fieldDefinitionUpdateStruct->fieldSettings
            )
        );
        // @todo fieldid missing in update struct?
        $spiFieldDefinition = new SPIFieldDefinition(
            array(
                "id" => null,
                "name" => $fieldDefinitionUpdateStruct->names,
                "description" => $fieldDefinitionUpdateStruct->descriptions,
                "identifier" => $fieldDefinitionUpdateStruct->identifier,
                "fieldGroup" => $fieldDefinitionUpdateStruct->fieldGroup,
                "position" => $fieldDefinitionUpdateStruct->position,
                "fieldType" => null,
                "isTranslatable" => $fieldDefinitionUpdateStruct->isTranslatable,
                "isRequired" => $fieldDefinitionUpdateStruct->isRequired,
                "isInfoCollector" => $fieldDefinitionUpdateStruct->isInfoCollector,
                "fieldTypeConstraints" => $fieldTypeConstraints,
                "defaultValue" => $fieldDefinitionUpdateStruct->defaultValue,
                "isSearchable" => $fieldDefinitionUpdateStruct->isSearchable
            )
        );

        $this->persistenceHandler->contentTypeHandler()->updateFieldDefinition(
            $contentTypeDraft->id,
            SPIContentType::STATUS_DRAFT,
            $spiFieldDefinition
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
        catch ( NotFoundException $e )
        {
            throw new BadStateException( '$contentTypeDraft', $e );
        }

        $this->persistenceHandler->contentTypeHandler()->publish(
            $loadedContentTypeDraft->id
        );
    }

    /**
     * instanciates a new content type group create class
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
     * instanciates a new content type create class
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

        return new ContentTypeCreateStruct;
    }

    /**
     * instanctiates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct()
    {
        return new ContentTypeUpdateStruct;
    }

    /**
     * instanciates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct()
    {
        return new ContentTypeGroupUpdateStruct;
    }

    /**
     * instanciates a field definition create struct
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
                "fieldTypeIdentifier" => $identifier,
                "identifier" => $fieldTypeIdentifier
            )
        );
    }

    /**
     * instanciates a field definition update class
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct()
    {
        return new FieldDefinitionUpdateStruct;
    }
}
