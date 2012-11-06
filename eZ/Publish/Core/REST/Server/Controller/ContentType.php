<?php
/**
 * File containing the ContentType controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use eZ\Publish\API\Repository\Exceptions\BadStateException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;

/**
 * ContentType controller
 */
class ContentType extends RestController
{
    /**
     * Content type service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct( ContentTypeService $contentTypeService )
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a new content type group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContentTypeGroup
     */
    public function createContentTypeGroup()
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        try
        {
            return new Values\CreatedContentTypeGroup(
                array(
                    'contentTypeGroup' => $this->contentTypeService->createContentTypeGroup( $createStruct )
                )
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }
    }

    /**
     * Updates a content type group
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function updateContentTypeGroup()
    {
        $urlValues = $this->urlHandler->parse( 'typegroup', $this->request->path );

        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        try
        {
            $this->contentTypeService->updateContentTypeGroup(
                $this->contentTypeService->loadContentTypeGroup( $urlValues['typegroup'] ),
                $this->mapToGroupUpdateStruct( $createStruct )
            );

            return $this->contentTypeService->loadContentTypeGroup( $urlValues['typegroup'] );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }
    }

    /**
     * The given content type group is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentTypeGroup()
    {
        $urlValues = $this->urlHandler->parse( 'typegroup', $this->request->path );

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup( $urlValues['typegroup'] );

        $contentTypes = $this->contentTypeService->loadContentTypes( $contentTypeGroup );
        if ( !empty( $contentTypes ) )
        {
            throw new ForbiddenException( 'Only empty content type groups can be deleted' );
        }

        $this->contentTypeService->deleteContentTypeGroup( $contentTypeGroup );

        return new Values\NoContent();
    }

    /**
     * Returns a list of content types of the group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeList|\eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList
     */
    public function listContentTypesForGroup()
    {
        $urlValues = $this->urlHandler->parse( 'grouptypes', $this->request->path );

        $contentTypes = $this->contentTypeService->loadContentTypes(
            $this->contentTypeService->loadContentTypeGroup( $urlValues['typegroup'] )
        );

        if ( $this->getMediaType( $this->request ) == 'application/vnd.ez.api.contenttypelist' )
        {
            return new Values\ContentTypeList( $contentTypes, $this->request->path );
        }

        return new Values\ContentTypeInfoList( $contentTypes, $this->request->path );
    }

    /**
     * Returns a list of all content type groups
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList
     */
    public function loadContentTypeGroupList()
    {
        return new Values\ContentTypeGroupList(
            $this->contentTypeService->loadContentTypeGroups()
        );
    }

    /**
     * Returns the content type group given by id
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup()
    {
        $urlValues = $this->urlHandler->parse( 'typegroup', $this->request->path );

        return $this->contentTypeService->loadContentTypeGroup( $urlValues['typegroup'] );
    }

    /**
     * Loads a content type
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function loadContentType()
    {
        $urlValues = $this->urlHandler->parse( 'type', $this->request->path );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );

        return new Values\RestContentType(
            $contentType,
            $contentType->getFieldDefinitions()
        );
    }

    /**
     * Returns a list of content types
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeList|\eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList
     */
    public function listContentTypes()
    {
        $contentTypes = array();

        if ( isset( $this->request->variables['identifier'] ) )
        {
            $contentTypes = array(
                $this->loadContentTypeByIdentifier()
            );
        }
        else if ( isset( $this->request->variables['remoteId'] ) )
        {
            $contentTypes = array(
                $this->loadContentTypeByRemoteId()
            );
        }

        if ( $this->getMediaType( $this->request ) == 'application/vnd.ez.api.contenttypelist' )
        {
            return new Values\ContentTypeList( $contentTypes, $this->request->path );
        }

        return new Values\ContentTypeInfoList( $contentTypes, $this->request->path );
    }

    /**
     * Loads a content type by its identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier()
    {
        return $this->contentTypeService->loadContentTypeByIdentifier(
            $this->request->variables['identifier']
        );
    }

    /**
     * Loads a content type by its remote ID
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId()
    {
        return $this->contentTypeService->loadContentTypeByRemoteId(
            $this->request->variables['remoteId']
        );
    }

    /**
     * Creates a new content type draft in the given content type group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContentType
     */
    public function createContentType()
    {
        $questionMarkPosition = strpos( $this->request->path, '?' );
        $urlValues = $this->urlHandler->parse(
            'grouptypes',
            $questionMarkPosition !== false ? substr( $this->request->path, 0, $questionMarkPosition ) : $this->request->path
        );

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup( $urlValues['typegroup'] );

        try
        {
            $contentTypeDraft = $this->contentTypeService->createContentType(
                $this->inputDispatcher->parse(
                    new Message(
                        array(
                            'Content-Type' => $this->request->contentType,
                        ),
                        $this->request->body
                    )
                ),
                array( $contentTypeGroup )
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        if ( isset( $this->request->variables['publish'] ) && $this->request->variables['publish'] === 'true' )
        {
            $this->contentTypeService->publishContentTypeDraft( $contentTypeDraft, 'bla' );

            $contentType = $this->contentTypeService->loadContentType( $contentTypeDraft->id );
            return new Values\CreatedContentType(
                array(
                    'contentType' => new Values\RestContentType(
                        $contentType,
                        $contentType->getFieldDefinitions()
                    )
                )
            );
        }

        return new Values\CreatedContentType(
            array(
                'contentType' => new Values\RestContentType(
                    $contentTypeDraft,
                    $contentTypeDraft->getFieldDefinitions()
                )
            )
        );
    }

    /**
     * Copies a content type. The identifier of the copy
     * is changed to copy_of_<identifier> and a new remoteId is generated.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copyContentType()
    {
        $urlValues = $this->urlHandler->parse( 'type', $this->request->path );

        $copiedContentType = $this->contentTypeService->copyContentType(
            $this->contentTypeService->loadContentType( $urlValues['type'] )
        );

        return new Values\ResourceCreated(
            $this->urlHandler->generate( 'type', array( 'type' => $copiedContentType->id ) )
        );
    }

    /**
     * Creates a draft and updates it with the given data
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContentType
     */
    public function createContentTypeDraft()
    {
        $urlValues = $this->urlHandler->parse( 'type', $this->request->path );
        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );

        try
        {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft(
                $contentType
            );
        }
        catch( BadStateException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $this->request->contentType,
                ),
                $this->request->body
            )
        );

        try
        {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\CreatedContentType(
            array(
                'contentType' => new Values\RestContentType(
                    // Reload the content type draft to get the updated values
                    $this->contentTypeService->loadContentTypeDraft(
                        $contentTypeDraft->id
                    )
                )
            )
        );
    }

    /**
     * Loads a content type draft
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function loadContentTypeDraft()
    {
        $urlValues = $this->urlHandler->parse( 'typeDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );

        return new Values\RestContentType(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()
        );
    }

    /**
     * Updates meta data of a draft. This method does not handle field definitions
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function updateContentTypeDraft()
    {
        $urlValues = $this->urlHandler->parse( 'typeDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );
        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $this->request->contentType,
                ),
                $this->request->body
            )
        );

        try
        {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\RestContentType(
            // Reload the content type draft to get the updated values
            $this->contentTypeService->loadContentTypeDraft(
                $contentTypeDraft->id
            )
        );
    }

    /**
     * Creates a new field definition for the given content type
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedFieldDefinition
     */
    public function addFieldDefinition()
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinitionsDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );
        $fieldDefinitionCreate = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $this->request->contentType,
                ),
                $this->request->body
            )
        );

        try
        {
            $this->contentTypeService->addFieldDefinition(
                $contentTypeDraft,
                $fieldDefinitionCreate
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );
        foreach ( $updatedDraft->getFieldDefinitions() as $fieldDefinition )
        {
            if ( $fieldDefinition->identifier == $fieldDefinitionCreate->identifier )
            {
                return new Values\CreatedFieldDefinition(
                    array(
                        'fieldDefinition' => new Values\RestFieldDefinition(
                            $updatedDraft, $fieldDefinition
                        )
                    )
                );
            }
        }

        throw new Exceptions\NotFoundException( "Field definition not found: '{$this->request->path}'." );
    }

    /**
     * Loads field definitions for a given content type
     *
     * @return \eZ\Publish\Core\REST\Server\Values\FieldDefinitionList
     */
    public function loadFieldDefinitionList()
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinitions', $this->request->path );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );

        return new Values\FieldDefinitionList(
            $contentType,
            $contentType->getFieldDefinitions()
        );
    }

    /**
     * Returns the field definition given by id
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestFieldDefinition
     */
    public function loadFieldDefinition()
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinition', $this->request->path );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );

        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            if ( $fieldDefinition->id == $urlValues['fieldDefinition'] )
            {
                return new Values\RestFieldDefinition(
                    $contentType,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFoundException( "Field definition not found: '{$this->request->path}'." );
    }

    /**
     * Loads field definitions for a given content type draft
     *
     * @return \eZ\Publish\Core\REST\Server\Values\FieldDefinitionList
     */
    public function loadDraftFieldDefinitionList()
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinitionsDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );

        return new Values\FieldDefinitionList(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()
        );
    }

    /**
     * Returns the draft field definition given by id
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestFieldDefinition
     */
    public function loadDraftFieldDefinition()
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinitionDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );

        foreach ( $contentTypeDraft->getFieldDefinitions() as $fieldDefinition )
        {
            if ( $fieldDefinition->id == $urlValues['fieldDefinition'] )
            {
                return new Values\RestFieldDefinition(
                    $contentTypeDraft,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFoundException( "Field definition not found: '{$this->request->path}'." );
    }

    /**
     * Updates the attributes of a field definition
     *
     * @return \eZ\Publish\Core\REST\Server\Values\FieldDefinitionList
     */
    public function updateFieldDefinition()
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinitionDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );
        $fieldDefinitionUpdate = $this->inputDispatcher->parse(
            new Message(
                array(
                    'Content-Type' => $this->request->contentType,
                ),
                $this->request->body
            )
        );

        $fieldDefinition = null;
        foreach ( $contentTypeDraft->getFieldDefinitions() as $fieldDef )
        {
            if ( $fieldDef->id == $urlValues['fieldDefinition'] )
            {
                $fieldDefinition = $fieldDef;
            }
        }

        if ( $fieldDefinition === null )
        {
            throw new Exceptions\NotFoundException( "Field definition not found: '{$this->request->path}'." );
        }

        try
        {
            $this->contentTypeService->updateFieldDefinition(
                $contentTypeDraft,
                $fieldDefinition,
                $fieldDefinitionUpdate
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );
        foreach ( $updatedDraft->getFieldDefinitions() as $fieldDef )
        {
            if ( $fieldDef->id == $urlValues['fieldDefinition'] )
            {
                return new Values\RestFieldDefinition(
                    $updatedDraft, $fieldDef
                );
            }
        }

        throw new Exceptions\NotFoundException( "Field definition not found: '{$this->request->path}'." );
    }

    /**
     * The given field definition is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function removeFieldDefinition()
    {
        $urlValues = $this->urlHandler->parse( 'typeFieldDefinitionDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );

        $fieldDefinition = null;
        foreach ( $contentTypeDraft->getFieldDefinitions() as $fieldDef )
        {
            if ( $fieldDef->id == $urlValues['fieldDefinition'] )
            {
                $fieldDefinition = $fieldDef;
            }
        }

        if ( $fieldDefinition === null )
        {
            throw new Exceptions\NotFoundException( "Field definition not found: '{$this->request->path}'." );
        }

        $this->contentTypeService->removeFieldDefinition(
            $contentTypeDraft,
            $fieldDefinition
        );

        return new Values\NoContent();
    }

    /**
     * Publishes a content type draft
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function publishContentTypeDraft()
    {
        $urlValues = $this->urlHandler->parse( 'typeDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );

        $fieldDefinitions = $contentTypeDraft->getFieldDefinitions();
        if ( empty( $fieldDefinitions ) )
        {
            throw new ForbiddenException( 'Empty content type draft cannot be published' );
        }

        $this->contentTypeService->publishContentTypeDraft( $contentTypeDraft );

        $publishedContentType = $this->contentTypeService->loadContentType( $contentTypeDraft->id );
        return new Values\RestContentType(
            $publishedContentType,
            $publishedContentType->getFieldDefinitions()
        );
    }

    /**
     * The given content type is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentType()
    {
        $urlValues = $this->urlHandler->parse( 'type', $this->request->path );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );

        try
        {
            $this->contentTypeService->deleteContentType( $contentType );
        }
        catch ( BadStateException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\NoContent();
    }

    /**
     * The given content type draft is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentTypeDraft()
    {
        $urlValues = $this->urlHandler->parse( 'typeDraft', $this->request->path );

        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft( $urlValues['type'] );
        $this->contentTypeService->deleteContentType( $contentTypeDraft );

        return new Values\NoContent();
    }

    /**
     * Returns the content type groups the content type belongs to
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList
     */
    public function loadGroupsOfContentType()
    {
        $urlValues = $this->urlHandler->parse( 'groupsOfType', $this->request->path );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );
        return new Values\ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    /**
     * Links a content type group to the content type and returns the updated group list
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList
     */
    public function linkContentTypeToGroup()
    {
        $questionMarkPosition = strpos( $this->request->path, '?' );
        $urlValues = $this->urlHandler->parse(
            'groupsOfType',
            $questionMarkPosition !== false ? substr( $this->request->path, 0, $questionMarkPosition ) : $this->request->path
        );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );

        try
        {
            $groupValues = $this->urlHandler->parse( 'typegroup', $this->request->variables['group'] );
        }
        catch ( Exceptions\InvalidArgumentException $e )
        {
            // Group URI does not match the required value
            throw new BadRequestException( $e->getMessage() );
        }

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup( $groupValues['typegroup'] );

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ( $existingContentTypeGroups as $existingGroup )
        {
            if ( $existingGroup->id == $contentTypeGroup->id )
            {
                $contentTypeInGroup = true;
                break;
            }
        }

        if ( $contentTypeInGroup )
        {
            throw new ForbiddenException( 'Content type is already linked to provided group' );
        }

        $this->contentTypeService->assignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $existingContentTypeGroups[] = $contentTypeGroup;
        return new Values\ContentTypeGroupRefList(
            $contentType,
            $existingContentTypeGroups
        );
    }

    /**
     * Removes the given group from the content type and returns the updated group list
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList
     */
    public function unlinkContentTypeFromGroup()
    {
        $urlValues = $this->urlHandler->parse( 'groupOfType', $this->request->path );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup( $urlValues['group'] );

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ( $existingContentTypeGroups as $existingGroup )
        {
            if ( $existingGroup->id == $contentTypeGroup->id )
            {
                $contentTypeInGroup = true;
                break;
            }
        }

        if ( !$contentTypeInGroup )
        {
            throw new Exceptions\NotFoundException( 'Content type is not in the given group' );
        }

        if ( count( $existingContentTypeGroups ) == 1 )
        {
            throw new ForbiddenException( 'Content type cannot be unlinked from the only remaining group' );
        }

        $this->contentTypeService->unassignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $contentType = $this->contentTypeService->loadContentType( $urlValues['type'] );
        return new Values\ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    /**
     * Converts the provided ContentTypeGroupCreateStruct to ContentTypeGroupUpdateStruct
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $createStruct
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    private function mapToGroupUpdateStruct( ContentTypeGroupCreateStruct $createStruct )
    {
        return new ContentTypeGroupUpdateStruct(
            array(
                'identifier' => $createStruct->identifier,
                'modifierId' => $createStruct->creatorId,
                'modificationDate' => $createStruct->creationDate,
                'mainLanguageCode' => $createStruct->mainLanguageCode,
                'names' => $createStruct->names,
                'descriptions' => $createStruct->descriptions
            )
        );
    }

    /**
     * Extracts the requested media type from $request
     *
     * @return string
     */
    private function getMediaType()
    {
        foreach ( $this->request->mimetype as $mimeType )
        {
            if ( preg_match( '(^([a-z0-9-/.]+)\+.*$)', $mimeType['value'], $matches ) )
            {
                return $matches[1];
            }
        }
        return 'unknown/unknown';
    }
}
