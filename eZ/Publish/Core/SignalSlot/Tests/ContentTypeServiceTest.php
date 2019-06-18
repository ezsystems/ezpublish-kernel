<?php

/**
 * File containing the ContentServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\ContentTypeService;

class ContentTypeServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\ContentTypeService'
        );
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new ContentTypeService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $userId = 14;
        $userVersionNo = 2;
        $contentTypeGroupId = 1;
        $contentTypeGroupIdentifier = 'Content';
        $contentTypeId = 42;
        $contentTypeIdentifier = 'ring';
        $contentTypeRemoteId = md5('The rings');
        $copyContentTypeId = 43;
        $copyContentTypeIdentifier = 'ring2';
        $copyContentTypeRemoteId = md5('The rings 2');
        $fieldDefinitionId = 242;
        $fieldDefinitionIdentifier = 'power';

        $contentTypeGroupCreateStruct = new ContentTypeGroupCreateStruct();
        $contentTypeGroupUpdateStruct = new ContentTypeGroupUpdateStruct();
        $contentTypeGroup = new ContentTypeGroup(
            [
                'id' => $contentTypeGroupId,
                'identifier' => $contentTypeGroupIdentifier,
            ]
        );
        $contentTypeCreateStruct = new ContentTypeCreateStruct();
        $contentTypeUpdateStruct = new ContentTypeUpdateStruct();
        $contentType = new ContentType(
            [
                'id' => $contentTypeId,
                'identifier' => $contentTypeIdentifier,
                'remoteId' => $contentTypeRemoteId,
                'fieldDefinitions' => [],
            ]
        );
        $contentTypeDraft = new ContentTypeDraft(
            [
                'innerContentType' => $contentType,
            ]
        );
        $copyContentType = new ContentType(
            [
                'id' => $copyContentTypeId,
                'identifier' => $copyContentTypeIdentifier,
                'remoteId' => $copyContentTypeRemoteId,
                'fieldDefinitions' => [],
            ]
        );
        $user = $this->getUser($userId, md5('Sauron'), $userVersionNo);

        $fieldDefinitionCreateStruct = new FieldDefinitionCreateStruct();
        $fieldDefinitionUpdateStruct = new FieldDefinitionUpdateStruct();
        $fieldDefinition = new FieldDefinition(
            [
                'id' => $fieldDefinitionId,
                'identifier' => $fieldDefinitionIdentifier,
            ]
        );

        return [
            [
                'createContentTypeGroup',
                [$contentTypeGroupCreateStruct],
                $contentTypeGroup,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\CreateContentTypeGroupSignal',
                ['groupId' => $contentTypeGroupId],
            ],
            [
                'loadContentTypeGroup',
                [$contentTypeGroupId],
                $contentTypeGroup,
                0,
            ],
            [
                'loadContentTypeGroupByIdentifier',
                [$contentTypeGroupIdentifier],
                $contentTypeGroup,
                0,
            ],
            [
                'loadContentTypeGroups',
                [],
                [$contentTypeGroup],
                0,
            ],
            [
                'updateContentTypeGroup',
                [$contentTypeGroup, $contentTypeGroupUpdateStruct],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\UpdateContentTypeGroupSignal',
                ['contentTypeGroupId' => $contentTypeGroupId],
            ],
            [
                'deleteContentTypeGroup',
                [$contentTypeGroup],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\DeleteContentTypeGroupSignal',
                ['contentTypeGroupId' => $contentTypeGroupId],
            ],
            [
                'createContentType',
                [$contentTypeCreateStruct, [$contentTypeGroup]],
                $contentType,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\CreateContentTypeSignal',
                ['contentTypeId' => $contentTypeId],
            ],
            [
                'loadContentType',
                [$contentTypeId],
                [$contentType],
                0,
            ],
            [
                'loadContentTypeByIdentifier',
                [$contentTypeIdentifier],
                [$contentType],
                0,
            ],
            [
                'loadContentTypeByRemoteId',
                [$contentTypeRemoteId],
                [$contentType],
                0,
            ],
            [
                'loadContentTypeDraft',
                [$contentType],
                [$contentTypeDraft],
                0,
            ],
            [
                'loadContentTypes',
                [$contentTypeGroup],
                [$contentType],
                0,
            ],
            [
                'createContentTypeDraft',
                [$contentType],
                $contentTypeDraft,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\CreateContentTypeDraftSignal',
                ['contentTypeId' => $contentTypeId],
            ],
            [
                'updateContentTypeDraft',
                [$contentTypeDraft, $contentTypeUpdateStruct],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\UpdateContentTypeDraftSignal',
                ['contentTypeDraftId' => $contentTypeId],
            ],
            [
                'deleteContentType',
                [$contentType],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\DeleteContentTypeSignal',
                ['contentTypeId' => $contentTypeId],
            ],
            [
                'copyContentType',
                [$contentType, $user],
                $copyContentType,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\CopyContentTypeSignal',
                [
                    'contentTypeId' => $contentTypeId,
                    'userId' => $userId,
                ],
            ],
            [
                'assignContentTypeGroup',
                [$contentType, $contentTypeGroup],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\AssignContentTypeGroupSignal',
                [
                    'contentTypeId' => $contentTypeId,
                    'contentTypeGroupId' => $contentTypeGroupId,
                ],
            ],
            [
                'unassignContentTypeGroup',
                [$contentType, $contentTypeGroup],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\UnassignContentTypeGroupSignal',
                [
                    'contentTypeId' => $contentTypeId,
                    'contentTypeGroupId' => $contentTypeGroupId,
                ],
            ],
            [
                'addFieldDefinition',
                [$contentTypeDraft, $fieldDefinitionCreateStruct],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\AddFieldDefinitionSignal',
                [
                    'contentTypeDraftId' => $contentTypeId,
                ],
            ],
            [
                'removeFieldDefinition',
                [$contentTypeDraft, $fieldDefinition],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\RemoveFieldDefinitionSignal',
                [
                    'contentTypeDraftId' => $contentTypeId,
                    'fieldDefinitionId' => $fieldDefinitionId,
                ],
            ],
            [
                'updateFieldDefinition',
                [$contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\UpdateFieldDefinitionSignal',
                [
                    'contentTypeDraftId' => $contentTypeId,
                    'fieldDefinitionId' => $fieldDefinitionId,
                ],
            ],
            [
                'publishContentTypeDraft',
                [$contentTypeDraft],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\PublishContentTypeDraftSignal',
                [
                    'contentTypeDraftId' => $contentTypeId,
                ],
            ],
            [
                'newContentTypeGroupCreateStruct',
                ['Content'],
                [$contentTypeGroupCreateStruct],
                0,
            ],
            [
                'newContentTypeCreateStruct',
                ['lords'],
                [$contentTypeCreateStruct],
                0,
            ],
            [
                'newContentTypeUpdateStruct',
                [],
                [$contentTypeUpdateStruct],
                0,
            ],
            [
                'newContentTypeGroupUpdateStruct',
                [],
                [$contentTypeGroupUpdateStruct],
                0,
            ],
            [
                'newFieldDefinitionCreateStruct',
                ['ezstring', 'name'],
                [$fieldDefinitionCreateStruct],
                0,
            ],
            [
                'newFieldDefinitionUpdateStruct',
                [],
                [$fieldDefinitionUpdateStruct],
                0,
            ],
            [
                'isContentTypeUsed',
                [$contentType],
                true,
                0,
            ],
        ];
    }
}
