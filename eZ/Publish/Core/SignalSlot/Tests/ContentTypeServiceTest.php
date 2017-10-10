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
            array(
                'id' => $contentTypeGroupId,
                'identifier' => $contentTypeGroupIdentifier,
            )
        );
        $contentTypeCreateStruct = new ContentTypeCreateStruct();
        $contentTypeUpdateStruct = new ContentTypeUpdateStruct();
        $contentType = new ContentType(
            array(
                'id' => $contentTypeId,
                'identifier' => $contentTypeIdentifier,
                'remoteId' => $contentTypeRemoteId,
                'fieldDefinitions' => array(),
            )
        );
        $contentTypeDraft = new ContentTypeDraft(
            array(
                'innerContentType' => $contentType,
            )
        );
        $copyContentType = new ContentType(
            array(
                'id' => $copyContentTypeId,
                'identifier' => $copyContentTypeIdentifier,
                'remoteId' => $copyContentTypeRemoteId,
                'fieldDefinitions' => array(),
            )
        );
        $user = $this->getUser($userId, md5('Sauron'), $userVersionNo);

        $fieldDefinitionCreateStruct = new FieldDefinitionCreateStruct();
        $fieldDefinitionUpdateStruct = new FieldDefinitionUpdateStruct();
        $fieldDefinition = new FieldDefinition(
            array(
                'id' => $fieldDefinitionId,
                'identifier' => $fieldDefinitionIdentifier,
            )
        );

        return array(
            array(
                'createContentTypeGroup',
                array($contentTypeGroupCreateStruct),
                $contentTypeGroup,
                1,
                ContentTypeServiceSignals\CreateContentTypeGroupSignal::class,
                array('groupId' => $contentTypeGroupId),
            ),
            array(
                'loadContentTypeGroup',
                array($contentTypeGroupId, ['eng-GB']),
                $contentTypeGroup,
                0,
            ),
            array(
                'loadContentTypeGroupByIdentifier',
                array($contentTypeGroupIdentifier, ['eng-GB']),
                $contentTypeGroup,
                0,
            ),
            array(
                'loadContentTypeGroups',
                array(['eng-GB']),
                array($contentTypeGroup),
                0,
            ),
            array(
                'updateContentTypeGroup',
                array($contentTypeGroup, $contentTypeGroupUpdateStruct),
                null,
                1,
                ContentTypeServiceSignals\UpdateContentTypeGroupSignal::class,
                array('contentTypeGroupId' => $contentTypeGroupId),
            ),
            array(
                'deleteContentTypeGroup',
                array($contentTypeGroup),
                null,
                1,
                ContentTypeServiceSignals\DeleteContentTypeGroupSignal::class,
                array('contentTypeGroupId' => $contentTypeGroupId),
            ),
            array(
                'createContentType',
                array($contentTypeCreateStruct, array($contentTypeGroup)),
                $contentType,
                1,
                ContentTypeServiceSignals\CreateContentTypeSignal::class,
                array('contentTypeId' => $contentTypeId),
            ),
            array(
                'loadContentType',
                array($contentTypeId, ['eng-GB']),
                array($contentType),
                0,
            ),
            array(
                'loadContentTypeByIdentifier',
                array($contentTypeIdentifier, ['eng-GB']),
                array($contentType),
                0,
            ),
            array(
                'loadContentTypeByRemoteId',
                array($contentTypeRemoteId, ['eng-GB']),
                array($contentType),
                0,
            ),
            array(
                'loadContentTypeDraft',
                array($contentType),
                array($contentTypeDraft),
                0,
            ),
            array(
                'loadContentTypes',
                array($contentTypeGroup, ['eng-GB']),
                array($contentType),
                0,
            ),
            array(
                'createContentTypeDraft',
                array($contentType),
                $contentTypeDraft,
                1,
                ContentTypeServiceSignals\CreateContentTypeDraftSignal::class,
                array('contentTypeId' => $contentTypeId),
            ),
            array(
                'updateContentTypeDraft',
                array($contentTypeDraft, $contentTypeUpdateStruct),
                null,
                1,
                ContentTypeServiceSignals\UpdateContentTypeDraftSignal::class,
                array('contentTypeDraftId' => $contentTypeId),
            ),
            array(
                'deleteContentType',
                array($contentType),
                null,
                1,
                ContentTypeServiceSignals\DeleteContentTypeSignal::class,
                array('contentTypeId' => $contentTypeId),
            ),
            array(
                'copyContentType',
                array($contentType, $user),
                $copyContentType,
                1,
                ContentTypeServiceSignals\CopyContentTypeSignal::class,
                array(
                    'contentTypeId' => $contentTypeId,
                    'userId' => $userId,
                ),
            ),
            array(
                'assignContentTypeGroup',
                array($contentType, $contentTypeGroup),
                null,
                1,
                ContentTypeServiceSignals\AssignContentTypeGroupSignal::class,
                array(
                    'contentTypeId' => $contentTypeId,
                    'contentTypeGroupId' => $contentTypeGroupId,
                ),
            ),
            array(
                'unassignContentTypeGroup',
                array($contentType, $contentTypeGroup),
                null,
                1,
                ContentTypeServiceSignals\UnassignContentTypeGroupSignal::class,
                array(
                    'contentTypeId' => $contentTypeId,
                    'contentTypeGroupId' => $contentTypeGroupId,
                ),
            ),
            array(
                'addFieldDefinition',
                array($contentTypeDraft, $fieldDefinitionCreateStruct),
                null,
                1,
                ContentTypeServiceSignals\AddFieldDefinitionSignal::class,
                array(
                    'contentTypeDraftId' => $contentTypeId,
                ),
            ),
            array(
                'removeFieldDefinition',
                array($contentTypeDraft, $fieldDefinition),
                null,
                1,
                ContentTypeServiceSignals\RemoveFieldDefinitionSignal::class,
                array(
                    'contentTypeDraftId' => $contentTypeId,
                    'fieldDefinitionId' => $fieldDefinitionId,
                ),
            ),
            array(
                'updateFieldDefinition',
                array($contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct),
                null,
                1,
                ContentTypeServiceSignals\UpdateFieldDefinitionSignal::class,
                array(
                    'contentTypeDraftId' => $contentTypeId,
                    'fieldDefinitionId' => $fieldDefinitionId,
                ),
            ),
            array(
                'publishContentTypeDraft',
                array($contentTypeDraft),
                null,
                1,
                ContentTypeServiceSignals\PublishContentTypeDraftSignal::class,
                array(
                    'contentTypeDraftId' => $contentTypeId,
                ),
            ),
            array(
                'newContentTypeGroupCreateStruct',
                array('Content'),
                array($contentTypeGroupCreateStruct),
                0,
            ),
            array(
                'newContentTypeCreateStruct',
                array('lords'),
                array($contentTypeCreateStruct),
                0,
            ),
            array(
                'newContentTypeUpdateStruct',
                array(),
                array($contentTypeUpdateStruct),
                0,
            ),
            array(
                'newContentTypeGroupUpdateStruct',
                array(),
                array($contentTypeGroupUpdateStruct),
                0,
            ),
            array(
                'newFieldDefinitionCreateStruct',
                array('ezstring', 'name'),
                array($fieldDefinitionCreateStruct),
                0,
            ),
            array(
                'newFieldDefinitionUpdateStruct',
                array(),
                array($fieldDefinitionUpdateStruct),
                0,
            ),
            array(
                'isContentTypeUsed',
                array($contentType),
                true,
                0,
            ),
        );
    }
}
