<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\HttpCache\Controller;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\REST\Server\Values\CachedValue;
use eZ\Publish\Core\REST\Server\Values\TemporaryRedirect;
use Symfony\Component\HttpFoundation\Request;

class ContentTypeController extends AbstractController
{
    /**
     * @var \eZ\Publish\Core\REST\Server\Controller\ContentType
     */
    private $innerController;

    /**
     * @param \eZ\Publish\Core\REST\Server\Controller\ContentType $innerController
     */
    public function __construct($innerController)
    {
        $this->innerController = $innerController;
    }

    public function createContentTypeGroup(Request $request)
    {
        return $this->innerController->createContentTypeGroup($request);
    }

    public function updateContentTypeGroup($contentTypeGroupId, Request $request)
    {
        return $this->innerController->updateContentTypeGroup($contentTypeGroupId, $request);
    }

    public function listContentTypesForGroup($contentTypeGroupId, Request $request)
    {
        $contentTypesList = $this->innerController->listContentTypesForGroup($contentTypeGroupId, $request);

        return new CachedValue(
            $contentTypesList,
            [
                'content-type' => array_map(
                    function (ContentType $contentType) {
                        return $contentType->id;
                    },
                    $contentTypesList->contentTypes
                ),
                'content-type-group' => $contentTypeGroupId,
            ]
        );
    }

    public function deleteContentTypeGroup($contentTypeGroupId)
    {
        return $this->innerController->deleteContentTypeGroup($contentTypeGroupId);
    }

    public function loadContentTypeGroupList(Request $request)
    {
        $contentTypeGroupList = $this->innerController->loadContentTypeGroupList($request);

        if ($contentTypeGroupList instanceof TemporaryRedirect) {
            return $contentTypeGroupList;
        }

        return new CachedValue(
            $contentTypeGroupList,
            [
                'content-type-group' => array_map(
                    function (ContentTypeGroup $contentTypeGroup) {
                        return $contentTypeGroup->id;
                    },
                    $contentTypeGroupList->contentTypeGroups
                ),
            ]
        );
    }

    public function loadContentTypeGroup($contentTypeGroupId)
    {
        $contentTypeGroup = $this->innerController->loadContentTypeGroup($contentTypeGroupId);

        return new CachedValue(
            $contentTypeGroup,
            ['content-type-group' => $contentTypeGroupId]
        );
    }

    public function loadContentType($contentTypeId)
    {
        $contentType = $this->innerController->loadContentType($contentTypeId);

        return new CachedValue(
            $contentType,
            ['content-type' => $contentTypeId]
        );
    }

    public function listContentTypes(Request $request)
    {
        $contentTypesList = $this->innerController->listContentTypes($request);

        return new CachedValue(
            $contentTypesList,
            [
                'content-type' => array_map(
                    function (ContentType $contentType) {
                        return $contentType->id;
                    },
                    $contentTypesList->contentTypes
                ),
            ]
        );
    }

    public function loadContentTypeByIdentifier(Request $request)
    {
        $contentType = $this->innerController->loadContentTypeByIdentifier($request);

        return new CachedValue(
            $contentType,
            ['content-type' => $contentType->id]
        );
    }

    public function loadContentTypeByRemoteId(Request $request)
    {
        $contentType = $this->innerController->loadContentTypeByRemoteId($request);

        return new CachedValue(
            $contentType,
            ['content-type' => $contentType->id]
        );
    }

    public function createContentType($contentTypeGroupId, Request $request)
    {
        return $this->innerController->createContentType($contentTypeGroupId, $request);
    }

    public function copyContentType($contentTypeId)
    {
        return $this->innerController->copyContentType($contentTypeId);
    }

    public function createContentTypeDraft($contentTypeId, Request $request)
    {
        return $this->innerController->createContentTypeDraft($contentTypeId, $request);
    }

    public function loadContentTypeDraft($contentTypeId)
    {
        return $this->innerController->loadContentTypeDraft($contentTypeId);
    }

    public function updateContentTypeDraft($contentTypeId, Request $request)
    {
        return $this->innerController->updateContentTypeDraft($contentTypeId, $request);
    }

    public function addContentTypeDraftFieldDefinition($contentTypeId, Request $request)
    {
        return $this->innerController->addContentTypeDraftFieldDefinition($contentTypeId, $request);
    }

    public function loadContentTypeFieldDefinitionList($contentTypeId)
    {
        $fieldDefinitionList = $this->innerController->loadContentTypeFieldDefinitionList($contentTypeId);

        return new CachedValue(
            $fieldDefinitionList,
            ['content-type' => $contentTypeId]
        );
    }

    public function loadContentTypeFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        return new CachedValue(
            $this->innerController->loadContentTypeFieldDefinition($contentTypeId, $fieldDefinitionId, $request),
            ['content-type' => $contentTypeId]
        );
    }

    public function loadContentTypeDraftFieldDefinitionList($contentTypeId)
    {
        return $this->innerController->loadContentTypeDraftFieldDefinitionList($contentTypeId);
    }

    public function loadContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        return $this->innerController->loadContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, $request);
    }

    public function updateContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        return $this->innerController->updateContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, $request);
    }

    public function removeContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        return $this->innerController->removeContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, $request);
    }

    public function publishContentTypeDraft($contentTypeId)
    {
        return $this->innerController->publishContentTypeDraft($contentTypeId);
    }

    public function deleteContentType($contentTypeId)
    {
        return $this->innerController->deleteContentType($contentTypeId);
    }

    public function deleteContentTypeDraft($contentTypeId)
    {
        return $this->innerController->deleteContentTypeDraft($contentTypeId);
    }

    public function loadGroupsOfContentType($contentTypeId)
    {
        return new CachedValue(
            $this->innerController->loadGroupsOfContentType($contentTypeId),
            ['content-type' => $contentTypeId]
        );
    }

    public function linkContentTypeToGroup($contentTypeId, Request $request)
    {
        return $this->innerController->linkContentTypeToGroup($contentTypeId, $request);
    }

    public function unlinkContentTypeFromGroup($contentTypeId, $contentTypeGroupId)
    {
        return $this->innerController->unlinkContentTypeFromGroup($contentTypeId, $contentTypeGroupId);
    }
}
