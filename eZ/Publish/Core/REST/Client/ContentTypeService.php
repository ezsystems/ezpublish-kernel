<?php

/**
 * File containing the ContentTypeService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
use eZ\Publish\Core\REST\Client\Values\ContentType\ContentType as RestContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Input\Dispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Message; use eZ\Publish\Core\REST\Client\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Exceptions\ForbiddenException;
use eZ\Publish\Core\REST\Client\Exceptions\BadStateException;

/**
 * @example Examples/contenttype.php
 */
class ContentTypeService implements APIContentTypeService, Sessionable
{
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
     * @var \eZ\Publish\Core\REST\Common\RequestParser
     */
    private $requestParser;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     */
    public function __construct(HttpClient $client, Dispatcher $inputDispatcher, Visitor $outputVisitor, RequestParser $requestParser)
    {
        $this->client = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor = $outputVisitor;
        $this->requestParser = $requestParser;
    }

    /**
     * Set session ID.
     *
     * Only for testing
     *
     * @param mixed $id
     *
     * @private
     */
    public function setSession($id)
    {
        if ($this->outputVisitor instanceof Sessionable) {
            $this->outputVisitor->setSession($id);
        }
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
    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        $inputMessage = $this->outputVisitor->visit($contentTypeGroupCreateStruct);
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType('ContentTypeGroup');

        $result = $this->client->request(
            'POST',
            $this->requestParser->generate('typegroups'),
            $inputMessage
        );

        try {
            return $this->inputDispatcher->parse($result);
        } catch (ForbiddenException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroup($contentTypeGroupId, array $prioritizedLanguages = [])
    {
        $response = $this->client->request(
            'GET',
            $contentTypeGroupId,
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('Section'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier, array $prioritizedLanguages = [])
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('typegroupByIdentifier', array('typegroup' => $contentTypeGroupIdentifier)),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroupList'))
            )
        );

        if ($response->statusCode == 307) {
            $response = $this->client->request(
                'GET',
                $response->headers['Location'],
                new Message(
                    array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroup'))
                )
            );
        }

        return $this->inputDispatcher->parse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeGroups(array $prioritizedLanguages = [])
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('typegroups'),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroupList'))
            )
        );

        return $this->inputDispatcher->parse($response);
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
    public function updateContentTypeGroup(ContentTypeGroup $contentTypeGroup, ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct)
    {
        $inputMessage = $this->outputVisitor->visit($contentTypeGroupUpdateStruct);
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType('ContentTypeGroup');
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        // Should originally be PATCH, but PHP's shiny new internal web server
        // dies with it.
        $result = $this->client->request(
            'POST',
            $contentTypeGroup->id,
            $inputMessage
        );

        try {
            return $this->inputDispatcher->parse($result);
        } catch (ForbiddenException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode());
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
    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup)
    {
        $response = $this->client->request(
            'DELETE',
            $contentTypeGroup->id,
            new Message(
                // @todo: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "Section" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroup'))
            )
        );

        if (!empty($response->body)) {
            try {
                return $this->inputDispatcher->parse($response);
            } catch (ForbiddenException $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode());
            }
        }
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
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @param array $contentTypeGroups Required array of {@link ContentTypeGroup} to link type with (must contain one)
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function createContentType(APIContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups)
    {
        $inputMessage = $this->outputVisitor->visit($contentTypeCreateStruct);
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType('ContentType');

        if (empty($contentTypeGroups)) {
            throw new InvalidArgumentException(
                "Argument '\$contentTypeGroups' is invalid: Argument must contain at least one ContentTypeGroup"
            );
        }

        /** @var $firstGroup \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup */
        /* @var $contentTypeGroups \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] */
        $firstGroup = array_pop($contentTypeGroups);
        $response = $this->client->request(
            'POST',
            $this->requestParser->generate(
                'grouptypes',
                $this->requestParser->parse('typegroup', $firstGroup->id)
            ),
            $inputMessage
        );

        try {
            $contentType = $this->inputDispatcher->parse($response);
        } catch (ForbiddenException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode());
        }

        foreach ($contentTypeGroups as $contentTypeGroup) {
            $this->assignContentTypeGroup($contentType, $contentTypeGroup);
        }

        return $this->completeContentType($contentType);
    }

    /**
     * TODO: ContentTypeGroupList reference should really be already available in the ContentType returned from server, so this method can be removed.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function completeContentType(ContentType $contentType)
    {
        // TODO: currently no way to fetch groups of a type draft
        if ($contentType instanceof ContentTypeDraft) {
            return $contentType;
        }

        $response = $this->client->request(
            'GET',
            $this->requestParser->generate(
                'groupsOfType',
                $this->requestParser->parse('type', $contentType->id)
            ),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroupRefList'))
            )
        );

        /** @var $referenceList \eZ\Publish\Core\REST\Client\Values\ContentTypeGroupRefList */
        $referenceList = $this->inputDispatcher->parse($response);

        return new RestContentType(
            $this,
            array(
                'id' => $contentType->id,
                'remoteId' => $contentType->remoteId,
                'identifier' => $contentType->identifier,
                'creatorId' => $contentType->creatorId,
                'modifierId' => $contentType->modifierId,
                'creationDate' => $contentType->creationDate,
                'modificationDate' => $contentType->modificationDate,
                'defaultSortField' => $contentType->defaultSortField,
                'defaultSortOrder' => $contentType->defaultSortOrder,
                'defaultAlwaysAvailable' => $contentType->defaultAlwaysAvailable,
                'names' => $contentType->names,
                'descriptions' => $contentType->descriptions,
                'isContainer' => $contentType->isContainer,
                'mainLanguageCode' => $contentType->mainLanguageCode,
                'nameSchema' => $contentType->nameSchema,
                'urlAliasSchema' => $contentType->urlAliasSchema,
                'status' => $contentType->status,

                'fieldDefinitionListReference' => $contentType->fieldDefinitionListReference,
                'contentTypeGroupListReference' => $referenceList->listReference,

                // dynamic
                //"fieldDefinitions" => $contentType->fieldDefinitions,
                //"contentTypeGroups" => $contentType->contentTypeGroups,
            )
        );
    }

    /**
     * Checks if the given response is an error.
     *
     * @param Message $response
     *
     * @return bool
     */
    protected function isErrorResponse(Message $response)
    {
        return (
            strpos($response->headers['Content-Type'], 'application/vnd.ez.api.ErrorMessage') === 0
        );
    }

    /**
     * Get a Content Type object draft by id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content type draft owned by the current user can not be found
     *
     * @param mixed $contentTypeId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    public function loadContentTypeDraft($contentTypeId)
    {
        $response = $this->client->request(
            'GET',
            $contentTypeId,
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentType'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * Update a Content Type object.
     *
     * Does not update fields (fieldDefinitions), use {@link updateFieldDefinition()} to update them.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the given identifier or remoteId already exists or there is no draft assigned to the authenticated user
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     */
    public function updateContentTypeDraft(ContentTypeDraft $contentTypeDraft, ContentTypeUpdateStruct $contentTypeUpdateStruct)
    {
        throw new \RuntimeException('@todo: Implement.');
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
    public function addFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinitionCreateStruct $fieldDefinitionCreateStruct)
    {
        throw new \RuntimeException('@todo: Implement.');
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
    public function removeFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition)
    {
        throw new \RuntimeException('@todo: Implement.');
    }

    /**
     * Update a field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field id in the update struct is not found or does not belong to the content type
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit a content type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the given identifier is used in an existing field of the given content type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft the content type draft
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition the field definition which should be updated
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function updateFieldDefinition(ContentTypeDraft $contentTypeDraft, FieldDefinition $fieldDefinition, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct)
    {
        throw new \RuntimeException('@todo: Implement.');
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
    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft)
    {
        throw new \RuntimeException('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentType($contentTypeId, array $prioritizedLanguages = [])
    {
        $response = $this->client->request(
            'GET',
            $contentTypeId,
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentType'))
            )
        );

        return $this->completeContentType($this->inputDispatcher->parse($response));
    }

    /**
     * Loads a single field definition by $fieldDefinitionId.
     *
     * ATTENTION: This is not an API method and only meant for internal use in
     * the REST Client implementation.
     *
     * @param string $fieldDefinitionId
     *
     * @return FieldDefinition
     */
    public function loadFieldDefinition($fieldDefinitionId)
    {
        $response = $this->client->request(
            'GET',
            $fieldDefinitionId,
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('FieldDefinition'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * Loads the FieldDefinitionList stored at $fieldDefinitionListReference.
     *
     * ATTENTION: This is not an API method and only meant for internal use in
     * the REST Client implementation.
     *
     * @param mixed $fieldDefinitionListReference
     *
     * @return \eZ\Publish\Core\REST\Client\Values\FieldDefinitionList
     */
    public function loadFieldDefinitionList($fieldDefinitionListReference)
    {
        $response = $this->client->request(
            'GET',
            $fieldDefinitionListReference,
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('FieldDefinitionList'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * Loads the ContentTypeGroupList stored at $contentTypeGroupListReference.
     *
     * ATTENTION: This is not an API method and only meant for internal use in
     * the REST Client implementation.
     *
     * @param mixed $contentTypeGroupListReference
     *
     * @return \eZ\Publish\Core\REST\Client\Values\ContentTypeGroupRefList
     */
    public function loadContentTypeGroupList($contentTypeGroupListReference)
    {
        $response = $this->client->request(
            'GET',
            $contentTypeGroupListReference,
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroupRefList'))
            )
        );

        return $this->inputDispatcher->parse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeByIdentifier($identifier, array $prioritizedLanguages = [])
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('typeByIdentifier', array('type' => $identifier)),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeList'))
            )
        );
        $contentTypes = $this->inputDispatcher->parse($response);

        return $this->completeContentType(reset($contentTypes));
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeByRemoteId($remoteId, array $prioritizedLanguages = [])
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate('typeByRemoteId', array('type' => $remoteId)),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeList'))
            )
        );
        $contentTypes = $this->inputDispatcher->parse($response);

        return reset($contentTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypeList(array $contentTypeIds, array $prioritizedLanguages = []): iterable
    {
        throw new \RuntimeException('@todo: Implement.');
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentTypes(ContentTypeGroup $contentTypeGroup, array $prioritizedLanguages = [])
    {
        $response = $this->client->request(
            'GET',
            $this->requestParser->generate(
                'grouptypes',
                $this->requestParser->parse('typegroup', $contentTypeGroup->id)
            ),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeList'))
            )
        );
        $completedContentTypes = array();
        $contentTypes = $this->inputDispatcher->parse($response);
        foreach ($contentTypes as $contentType) {
            $completedContentTypes[] = $this->completeContentType($contentType);
        }

        return $completedContentTypes;
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
    public function createContentTypeDraft(ContentType $contentType)
    {
        throw new \RuntimeException('@todo: Implement.');
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
    public function deleteContentType(ContentType $contentType)
    {
        throw new \RuntimeException('@todo: Implement.');
    }

    /**
     * Copy Type incl fields and groupIds to a new Type object.
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
    public function copyContentType(ContentType $contentType, User $user = null)
    {
        throw new \RuntimeException('@todo: Implement.');
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
    public function assignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup)
    {
        if ($contentType instanceof ContentTypeDraft) {
            $urlValues = $this->requestParser->parse('typeDraft', $contentType->id);
        } else {
            $urlValues = $this->requestParser->parse('type', $contentType->id);
        }
        $urlValues['group'] = $contentTypeGroup->id;

        $response = $this->client->request(
            'POST',
            $this->requestParser->generate('typeGroupAssign', $urlValues),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroupRefList'))
            )
        );

        if ($this->isErrorResponse($response)) {
            try {
                $this->inputDispatcher->parse($response);
            } catch (ForbiddenException $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode());
            }
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
    public function unassignContentTypeGroup(ContentType $contentType, ContentTypeGroup $contentTypeGroup)
    {
        if ($contentType instanceof ContentTypeDraft) {
            $urlValues = $this->requestParser->parse('typeDraft', $contentType->id);
        } else {
            $urlValues = $this->requestParser->parse('type', $contentType->id);
        }
        $groupUrlValues = $this->requestParser->parse('typegroup', $contentTypeGroup->id);
        $urlValues['group'] = $groupUrlValues['typegroup'];

        $response = $this->client->request(
            'DELETE',
            $this->requestParser->generate('groupOfType', $urlValues),
            new Message(
                array('Accept' => $this->outputVisitor->getMediaType('ContentTypeGroupRefList'))
            )
        );

        if ($this->isErrorResponse($response)) {
            try {
                $this->inputDispatcher->parse($response);
            } catch (ForbiddenException $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode());
            } catch (NotFoundException $e) {
                throw new BadStateException($e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * Instantiates a new content type group create class.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    public function newContentTypeGroupCreateStruct($identifier)
    {
        if (!is_string($identifier)) {
            throw new InvalidArgumentValue('$identifier', $identifier);
        }

        return new ContentTypeGroupCreateStruct(
            array(
                'identifier' => $identifier,
            )
        );
    }

    /**
     * Instantiates a new content type create class.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct($identifier)
    {
        if (!is_string($identifier)) {
            throw new InvalidArgumentValue('$identifier', $identifier);
        }

        return new ContentTypeCreateStruct(
            array(
                'identifier' => $identifier,
            )
        );
    }

    /**
     * Instantiates a new content type update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function newContentTypeUpdateStruct()
    {
        return new ContentTypeUpdateStruct();
    }

    /**
     * Instantiates a new content type update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    public function newContentTypeGroupUpdateStruct()
    {
        return new ContentTypeGroupUpdateStruct();
    }

    /**
     * Instantiates a field definition create struct.
     *
     * @param string $fieldTypeIdentifier the required field type identifier
     * @param string $identifier the required identifier for the field definition
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function newFieldDefinitionCreateStruct($identifier, $fieldTypeIdentifier)
    {
        if (!is_string($identifier)) {
            throw new InvalidArgumentValue('$identifier', $identifier);
        }

        if (!is_string($fieldTypeIdentifier)) {
            throw new InvalidArgumentValue('$fieldTypeIdentifier', $fieldTypeIdentifier);
        }

        return new FieldDefinitionCreateStruct(
            array(
                'identifier' => $identifier,
                'fieldTypeIdentifier' => $fieldTypeIdentifier,
            )
        );
    }

    /**
     * Instantiates a field definition update class.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function newFieldDefinitionUpdateStruct()
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
    public function isContentTypeUsed(ContentType $contentType)
    {
        throw new \Exception('@todo: Implement.');
    }
}
