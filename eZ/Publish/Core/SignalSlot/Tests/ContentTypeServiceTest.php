<?php

/**
 * File containing the ContentServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\ContentTypeService as APIContentTypeService;
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
use eZ\Publish\Core\SignalSlot\Signal\ContentTypeService as ContentTypeServiceSignals;

class ContentTypeServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIContentTypeService::class);
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
                ContentTypeServiceSignals\CreateContentTypeGroupSignal::class,
                ['groupId' => $contentTypeGroupId],
            ],
            [
                'loadContentTypeGroup',
                [$contentTypeGroupId, ['eng-GB']],
                $contentTypeGroup,
                0,
            ],
            [
                'loadContentTypeGroupByIdentifier',
                [$contentTypeGroupIdentifier, ['eng-GB']],
                $contentTypeGroup,
                0,
            ],
            [
                'loadContentTypeGroups',
                [['eng-GB']],
                [$contentTypeGroup],
                0,
            ],
            [
                'updateContentTypeGroup',
                [$contentTypeGroup, $contentTypeGroupUpdateStruct],
                null,
                1,
                ContentTypeServiceSignals\UpdateContentTypeGroupSignal::class,
                ['contentTypeGroupId' => $contentTypeGroupId],
            ],
            [
                'deleteContentTypeGroup',
                [$contentTypeGroup],
                null,
                1,
                ContentTypeServiceSignals\DeleteContentTypeGroupSignal::class,
                ['contentTypeGroupId' => $contentTypeGroupId],
            ],
            [
                'createContentType',
                [$contentTypeCreateStruct, [$contentTypeGroup]],
                $contentType,
                1,
                ContentTypeServiceSignals\CreateContentTypeSignal::class,
                ['contentTypeId' => $contentTypeId],
            ],
            [
                'loadContentType',
                [$contentTypeId, ['eng-GB']],
                [$contentType],
                0,
            ],
            [
                'loadContentTypeByIdentifier',
                [$contentTypeIdentifier, ['eng-GB']],
                [$contentType],
                0,
            ],
            [
                'loadContentTypeByRemoteId',
                [$contentTypeRemoteId, ['eng-GB']],
                [$contentType],
                0,
            ],
            [
                'loadContentTypeDraft',
                [$contentType, false],
                [$contentTypeDraft],
                0,
            ],
            [
                'loadContentTypeDraft',
                [$contentType, true],
                [$contentTypeDraft],
                0,
            ],
            [
                'loadContentTypes',
                [$contentTypeGroup, ['eng-GB']],
                [$contentType],
                0,
            ],
            [
                'createContentTypeDraft',
                [$contentType],
                $contentTypeDraft,
                1,
                ContentTypeServiceSignals\CreateContentTypeDraftSignal::class,
                ['contentTypeId' => $contentTypeId],
            ],
            [
                'updateContentTypeDraft',
                [$contentTypeDraft, $contentTypeUpdateStruct],
                null,
                1,
                ContentTypeServiceSignals\UpdateContentTypeDraftSignal::class,
                ['contentTypeDraftId' => $contentTypeId],
            ],
            [
                'deleteContentType',
                [$contentType],
                null,
                1,
                ContentTypeServiceSignals\DeleteContentTypeSignal::class,
                ['contentTypeId' => $contentTypeId],
            ],
            [
                'copyContentType',
                [$contentType, $user],
                $copyContentType,
                1,
                ContentTypeServiceSignals\CopyContentTypeSignal::class,
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
                ContentTypeServiceSignals\AssignContentTypeGroupSignal::class,
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
                ContentTypeServiceSignals\UnassignContentTypeGroupSignal::class,
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
                ContentTypeServiceSignals\AddFieldDefinitionSignal::class,
                [
                    'contentTypeDraftId' => $contentTypeId,
                ],
            ],
            [
                'removeFieldDefinition',
                [$contentTypeDraft, $fieldDefinition],
                null,
                1,
                ContentTypeServiceSignals\RemoveFieldDefinitionSignal::class,
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
                ContentTypeServiceSignals\UpdateFieldDefinitionSignal::class,
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
                ContentTypeServiceSignals\PublishContentTypeDraftSignal::class,
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
            [
                'removeContentTypeTranslation',
                [$contentTypeDraft, 'eng-GB'],
                $contentTypeDraft,
                1,
            ],
        ];
    }
}
