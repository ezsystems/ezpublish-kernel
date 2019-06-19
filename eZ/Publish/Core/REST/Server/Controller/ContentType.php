<?php

/**
 * File containing the ContentType controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use eZ\Publish\API\Repository\Exceptions\BadStateException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\ContentTypeValidationException;
use eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException;
use eZ\Publish\Core\REST\Server\Controller as RestController;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use Symfony\Component\HttpFoundation\Request;

/**
 * ContentType controller.
 */
class ContentType extends RestController
{
    /**
     * Content type service.
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Construct controller.
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a new content type group.
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContentTypeGroup
     */
    public function createContentTypeGroup(Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            return new Values\CreatedContentTypeGroup(
                [
                    'contentTypeGroup' => $this->contentTypeService->createContentTypeGroup($createStruct),
                ]
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }
    }

    /**
     * Updates a content type group.
     *
     * @param $contentTypeGroupId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function updateContentTypeGroup($contentTypeGroupId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeGroup(
                $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId),
                $this->mapToGroupUpdateStruct($createStruct)
            );

            return $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }
    }

    /**
     * Returns a list of content types of the group.
     *
     * @param string $contentTypeGroupId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeList|\eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList
     */
    public function listContentTypesForGroup($contentTypeGroupId, Request $request)
    {
        $contentTypes = $this->contentTypeService->loadContentTypes(
            $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL),
            Language::ALL
        );

        if ($this->getMediaType($request) === 'application/vnd.ez.api.contenttypelist') {
            return new Values\ContentTypeList($contentTypes, $request->getPathInfo());
        }

        return new Values\ContentTypeInfoList($contentTypes, $request->getPathInfo());
    }

    /**
     * The given content type group is deleted.
     *
     * @param mixed $contentTypeGroupId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentTypeGroup($contentTypeGroupId)
    {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup);
        if (!empty($contentTypes)) {
            throw new ForbiddenException('Only empty content type groups can be deleted');
        }

        $this->contentTypeService->deleteContentTypeGroup($contentTypeGroup);

        return new Values\NoContent();
    }

    /**
     * Returns a list of all content type groups.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList
     */
    public function loadContentTypeGroupList(Request $request)
    {
        if ($request->query->has('identifier')) {
            $contentTypeGroup = $this->contentTypeService->loadContentTypeGroupByIdentifier(
                $request->query->get('identifier')
            );

            return new Values\TemporaryRedirect(
                $this->router->generate(
                    'ezpublish_rest_loadContentTypeGroup',
                    [
                        'contentTypeGroupId' => $contentTypeGroup->id,
                    ]
                )
            );
        }

        return new Values\ContentTypeGroupList(
            $this->contentTypeService->loadContentTypeGroups(Language::ALL)
        );
    }

    /**
     * Returns the content type group given by id.
     *
     * @param $contentTypeGroupId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup($contentTypeGroupId)
    {
        return $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL);
    }

    /**
     * Loads a content type.
     *
     * @param $contentTypeId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function loadContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new Values\RestContentType(
            $contentType,
            $contentType->getFieldDefinitions()
        );
    }

    /**
     * Returns a list of content types.
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeList|\eZ\Publish\Core\REST\Server\Values\ContentTypeInfoList
     */
    public function listContentTypes(Request $request)
    {
        if ($this->getMediaType($request) === 'application/vnd.ez.api.contenttypelist') {
            $return = new Values\ContentTypeList([], $request->getPathInfo());
        } else {
            $return = new Values\ContentTypeInfoList([], $request->getPathInfo());
        }

        if ($request->query->has('identifier')) {
            $return->contentTypes = [$this->loadContentTypeByIdentifier($request)];

            return $return;
        }

        if ($request->query->has('remoteId')) {
            $return->contentTypes = [
                $this->loadContentTypeByRemoteId($request),
            ];

            return $return;
        }

        $limit = null;
        if ($request->query->has('limit')) {
            $limit = (int)$request->query->get('limit', null);
            if ($limit <= 0) {
                throw new BadRequestException('wrong value for limit parameter');
            }
        }
        $contentTypes = $this->getContentTypeList();
        $sort = $request->query->get('sort');
        if ($request->query->has('orderby')) {
            $orderby = $request->query->get('orderby');
            $this->sortContentTypeList($contentTypes, $orderby, $sort);
        }
        $offset = $request->query->get('offset', 0);
        $return->contentTypes = array_slice($contentTypes, $offset, $limit);

        return $return;
    }

    /**
     * Loads a content type by its identifier.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByIdentifier(
            $request->query->get('identifier'),
            Language::ALL
        );
    }

    /**
     * Loads a content type by its remote ID.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByRemoteId(
            $request->query->get('remoteId'),
            Language::ALL
        );
    }

    /**
     * Creates a new content type draft in the given content type group.
     *
     * @param $contentTypeGroupId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContentType
     */
    public function createContentType($contentTypeGroupId, Request $request)
    {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);
        $publish = ($request->query->has('publish') && $request->query->get('publish') === 'true');

        try {
            $contentTypeDraft = $this->contentTypeService->createContentType(
                $this->inputDispatcher->parse(
                    new Message(
                        [
                            'Content-Type' => $request->headers->get('Content-Type'),
                            // @todo Needs refactoring! Temporary solution so parser has access to get parameters
                            '__publish' => $publish,
                        ],
                        $request->getContent()
                    )
                ),
                [$contentTypeGroup]
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        } catch (ContentTypeValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Exceptions\Parser $e) {
            throw new BadRequestException($e->getMessage());
        }

        if ($publish) {
            $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

            $contentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

            return new Values\CreatedContentType(
                [
                    'contentType' => new Values\RestContentType(
                        $contentType,
                        $contentType->getFieldDefinitions()
                    ),
                ]
            );
        }

        return new Values\CreatedContentType(
            [
                'contentType' => new Values\RestContentType(
                    $contentTypeDraft,
                    $contentTypeDraft->getFieldDefinitions()
                ),
            ]
        );
    }

    /**
     * Copies a content type. The identifier of the copy is changed to
     * copy_of_<originalBaseIdentifier>_<newTypeId> and a new remoteId is generated.
     *
     * @param $contentTypeId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceCreated
     */
    public function copyContentType($contentTypeId)
    {
        $copiedContentType = $this->contentTypeService->copyContentType(
            $this->contentTypeService->loadContentType($contentTypeId)
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ezpublish_rest_loadContentType',
                ['contentTypeId' => $copiedContentType->id]
            )
        );
    }

    /**
     * Creates a draft and updates it with the given data.
     *
     * @param $contentTypeId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedContentType
     */
    public function createContentTypeDraft($contentTypeId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft(
                $contentType
            );
        } catch (BadStateException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        return new Values\CreatedContentType(
            [
                'contentType' => new Values\RestContentType(
                    // Reload the content type draft to get the updated values
                    $this->contentTypeService->loadContentTypeDraft(
                        $contentTypeDraft->id
                    )
                ),
            ]
        );
    }

    /**
     * Loads a content type draft.
     *
     * @param $contentTypeId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function loadContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        return new Values\RestContentType(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()
        );
    }

    /**
     * Updates meta data of a draft. This method does not handle field definitions.
     *
     * @param $contentTypeId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function updateContentTypeDraft($contentTypeId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        return new Values\RestContentType(
            // Reload the content type draft to get the updated values
            $this->contentTypeService->loadContentTypeDraft(
                $contentTypeDraft->id
            )
        );
    }

    /**
     * Creates a new field definition for the given content type draft.
     *
     * @param $contentTypeId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedFieldDefinition
     */
    public function addContentTypeDraftFieldDefinition($contentTypeId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $fieldDefinitionCreate = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->addFieldDefinition(
                $contentTypeDraft,
                $fieldDefinitionCreate
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (BadStateException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        foreach ($updatedDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->identifier == $fieldDefinitionCreate->identifier) {
                return new Values\CreatedFieldDefinition(
                    [
                        'fieldDefinition' => new Values\RestFieldDefinition($updatedDraft, $fieldDefinition),
                    ]
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Loads field definitions for a given content type.
     *
     * @param $contentTypeId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\FieldDefinitionList
     *
     * @todo Check why this isn't in the specs
     */
    public function loadContentTypeFieldDefinitionList($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new Values\FieldDefinitionList(
            $contentType,
            $contentType->getFieldDefinitions()
        );
    }

    /**
     * Returns the field definition given by id.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestFieldDefinition
     */
    public function loadContentTypeFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return new Values\RestFieldDefinition(
                    $contentType,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Loads field definitions for a given content type draft.
     *
     * @param $contentTypeId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\FieldDefinitionList
     */
    public function loadContentTypeDraftFieldDefinitionList($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        return new Values\FieldDefinitionList(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()
        );
    }

    /**
     * Returns the draft field definition given by id.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestFieldDefinition
     */
    public function loadContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return new Values\RestFieldDefinition(
                    $contentTypeDraft,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Updates the attributes of a field definition.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\FieldDefinitionList
     */
    public function updateContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $fieldDefinitionUpdate = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        $fieldDefinition = null;
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                $fieldDefinition = $fieldDef;
            }
        }

        if ($fieldDefinition === null) {
            throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
        }

        try {
            $this->contentTypeService->updateFieldDefinition(
                $contentTypeDraft,
                $fieldDefinition,
                $fieldDefinitionUpdate
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        foreach ($updatedDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                return new Values\RestFieldDefinition($updatedDraft, $fieldDef);
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Deletes a field definition from a content type draft.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function removeContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        $fieldDefinition = null;
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                $fieldDefinition = $fieldDef;
            }
        }

        if ($fieldDefinition === null) {
            throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
        }

        $this->contentTypeService->removeFieldDefinition(
            $contentTypeDraft,
            $fieldDefinition
        );

        return new Values\NoContent();
    }

    /**
     * Publishes a content type draft.
     *
     * @param $contentTypeId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestContentType
     */
    public function publishContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        $fieldDefinitions = $contentTypeDraft->getFieldDefinitions();
        if (empty($fieldDefinitions)) {
            throw new ForbiddenException('Empty content type draft cannot be published');
        }

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $publishedContentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

        return new Values\RestContentType(
            $publishedContentType,
            $publishedContentType->getFieldDefinitions()
        );
    }

    /**
     * The given content type is deleted.
     *
     * @param $contentTypeId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $this->contentTypeService->deleteContentType($contentType);
        } catch (BadStateException $e) {
            throw new ForbiddenException($e->getMessage());
        }

        return new Values\NoContent();
    }

    /**
     * The given content type draft is deleted.
     *
     * @param $contentTypeId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $this->contentTypeService->deleteContentType($contentTypeDraft);

        return new Values\NoContent();
    }

    /**
     * Returns the content type groups the content type belongs to.
     *
     * @param $contentTypeId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList
     */
    public function loadGroupsOfContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new Values\ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    /**
     * Links a content type group to the content type and returns the updated group list.
     *
     * @param mixed $contentTypeId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList
     */
    public function linkContentTypeToGroup($contentTypeId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $contentTypeGroupId = $this->requestParser->parseHref(
                $request->query->get('group'),
                'contentTypeGroupId'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            // Group URI does not match the required value
            throw new BadRequestException($e->getMessage());
        }

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if ($contentTypeInGroup) {
            throw new ForbiddenException('Content type is already linked to provided group');
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
     * Removes the given group from the content type and returns the updated group list.
     *
     * @param $contentTypeId
     * @param $contentTypeGroupId
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList
     */
    public function unlinkContentTypeFromGroup($contentTypeId, $contentTypeGroupId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if (!$contentTypeInGroup) {
            throw new Exceptions\NotFoundException('Content type is not in the given group');
        }

        if (count($existingContentTypeGroups) == 1) {
            throw new ForbiddenException('Content type cannot be unlinked from the only remaining group');
        }

        $this->contentTypeService->unassignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        return new Values\ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    /**
     * Converts the provided ContentTypeGroupCreateStruct to ContentTypeGroupUpdateStruct.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $createStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    private function mapToGroupUpdateStruct(ContentTypeGroupCreateStruct $createStruct)
    {
        return new ContentTypeGroupUpdateStruct(
            [
                'identifier' => $createStruct->identifier,
                'modifierId' => $createStruct->creatorId,
                'modificationDate' => $createStruct->creationDate,
            ]
        );
    }

    /**
     * @param array &$contentTypes
     * @param string $orderby
     *
     * @return mixed
     *
     * @throws \eZ\Publish\Core\REST\Server\Exceptions\BadRequestException
     */
    protected function sortContentTypeList(array &$contentTypes, $orderby, $sort = 'asc')
    {
        switch ($orderby) {
            case 'name':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        function (APIContentType $contentType1, APIContentType $contentType2) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier);
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        function (APIContentType $contentType1, APIContentType $contentType2) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier) * -1;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            case 'lastmodified':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        function ($timeObj3, $timeObj4) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 > $timeObj4;
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        function ($timeObj3, $timeObj4) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 < $timeObj4;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            default:
                throw new BadRequestException('wrong value for orderby parameter');
                break;
        }
    }

    /**
     * @return ContentType[]
     */
    protected function getContentTypeList()
    {
        $contentTypes = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            $contentTypes = array_merge(
                $contentTypes,
                $this->contentTypeService->loadContentTypes($contentTypeGroup, Language::ALL)
            );
        }

        return $contentTypes;
    }
}
