<?php
/**
 * File containing the ContentTypeService class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\ContentTypeService as APIContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct as APIContentTypeCreateStruct;
use eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;

use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Message;

use eZ\Publish\Core\REST\Client\Values;

use eZ\Publish\Core\REST\Client\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Exceptions\ForbiddenException;

/**
 * @example Examples/contenttype.php
 *
 * @package eZ\Publish\API\Repository
 */
class ContentTypeService implements APIContentTypeService, Sessionable
{
    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    private $urlHandler;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, UrlHandler $urlHandler )
    {
        $this->client          = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor   = $outputVisitor;
        $this->urlHandler      = $urlHandler;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed tringid
     *
     * @private
     *
     * @return void
     */
    public function setSession( $id )
    {
        if ( $this->outputVisitor instanceof Sessionable )
        {
            $this->outputVisitor->setSession( $id );
        }
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
    public function createContentTypeGroup( ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $contentTypeGroupCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'ContentTypeGroup' );

        $result = $this->client->request(
            'POST',
            $this->urlHandler->generate( 'typegroups' ),
            $inputMessage
        );

        try
        {
            return $this->inputDispatcher->parse( $result );
        }
        catch ( ForbiddenException $e )
        {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode() );
        }
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
        $response = $this->client->request(
            'GET',
            $contentTypeGroupId,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'Section' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
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
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'typegroups' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ContentTypeGroupList' ) )
            )
        );
        $contentTypeGroups = $this->inputDispatcher->parse( $response );

        foreach ( $contentTypeGroups as $contentTypeGroup )
        {
            if ( $contentTypeGroup->identifier == $contentTypeGroupIdentifier )
            {
                return $contentTypeGroup;
            }
        }

        // TODO this really needs /content/typegroups?identifier=blah
        throw new NotFoundException( "Could not find..." );
    }

    /**
     * Get all Content Type Groups
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function loadContentTypeGroups()
    {
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'typegroups' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ContentTypeGroupList' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
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
    public function updateContentTypeGroup( ContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $contentTypeGroupUpdateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'ContentTypeGroup' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        // Should originally be PATCH, but PHP's shiny new internal web server
        // dies with it.
        $result = $this->client->request(
            'POST',
            $contentTypeGroup->id,
            $inputMessage
        );

        try
        {
            return $this->inputDispatcher->parse( $result );
        }
        catch ( ForbiddenException $e )
        {
            throw new InvalidArgumentException( $e->getMessage(), $e->getCode() );
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
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function deleteContentTypeGroup( ContentTypeGroup $contentTypeGroup )
    {
        $response = $this->client->request(
            'DELETE',
            $contentTypeGroup->id,
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ContentTypeGroup' ) )
            )
        );

        if ( !empty( $response->body ) )
        {
            $this->inputDispatcher->parse( $response );
        }
    }

    /**
     * Create a Content Type object.
     *
     * The content type is created in the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the identifier or remoteId in the content type create struct already exists
     *         or there is a dublicate field identifier
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param array $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType( APIContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups )
    {
        throw new \Exception( "@todo: Implement." );
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
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Update a Content Type object
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier or remoteId already exists or there is no draft assigned to the authenticated user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function updateContentTypeDraft( ContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct )
    {
        throw new \Exception( "@todo: Implement." );
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
    public function addFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct )
    {
        throw new \Exception( "@todo: Implement." );
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
    public function removeFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Update a field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the given identifier is used in an existing field of the given content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition( ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct )
    {
        throw new \Exception( "@todo: Implement." );
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
    public function publishContentTypeDraft( ContentTypeDraft $contentTypeDraft )
    {
        throw new \Exception( "@todo: Implement." );
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
        $response = $this->client->request(
            'GET',
            $contentTypeId,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ContentType' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * Loads a single field definition by $fieldDefinitionId
     *
     * ATTENTION: This is not an API method and only meant for internal use in
     * the REST Client implementation.
     *
     * @param string $fieldDefinitionId
     *
     * @access protected
     *
     * @return FieldDefinition
     */
    public function loadFieldDefinition( $fieldDefinitionId )
    {
        $response = $this->client->request(
            'GET',
            $fieldDefinitionId,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'FieldDefinition' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * Loads the FieldDefinitionList stored at $fieldDefinitionListReference
     *
     * ATTENTION: This is not an API method and only meant for internal use in
     * the REST Client implementation.
     *
     * @param mixed $fieldDefinitionListReference
     *
     * @return FieldDefinitionList
     */
    public function loadFieldDefinitionList( $fieldDefinitionListReference )
    {
        $response = $this->client->request(
            'GET',
            $fieldDefinitionListReference,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'FieldDefinitionList' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
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
        throw new \Exception( "@todo: Implement." );
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
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Get Content Type objects which belong to the given content type group
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $contentTypeGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[] Which have status DEFINED
     */
    public function loadContentTypes( ContentTypeGroup $contentTypeGroup )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Creates a draft from an existing content type.
     *
     * This is a complete copy of the content
     * type wiich has the state STATUS_DRAFT.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If there is already a draft assigned to another user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentTypeDraft( ContentType $contentType )
    {
        throw new \Exception( "@todo: Implement." );
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
    public function deleteContentType( ContentType $contentType )
    {
        throw new \Exception( "@todo: Implement." );
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
    public function copyContentType( ContentType $contentType, User $user = null )
    {
        throw new \Exception( "@todo: Implement." );
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
    public function assignContentTypeGroup( ContentType $contentType, ContentTypeGroup $contentTypeGroup )
    {
        throw new \Exception( "@todo: Implement." );
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
    public function unassignContentTypeGroup( ContentType $contentType, ContentTypeGroup $contentTypeGroup )
    {
        throw new \Exception( "@todo: Implement." );
    }

    /**
     * Instantiates a new content type group create class
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
     * Instantiates a new content type create class
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
     * Instantiates a new content type update struct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct()
    {
        return new ContentTypeGroupUpdateStruct;
    }

    /**
     * Instantiates a field definition create struct
     *
     * @param string $fieldTypeIdentifier the required field type identifier
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
     * Instantiates a field definition update class
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct()
    {
        return new FieldDefinitionUpdateStruct;
    }
}
