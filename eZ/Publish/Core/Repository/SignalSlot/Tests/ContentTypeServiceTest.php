<?php
/**
 * File containing the ContentServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\SignalSlot\Tests;

use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;

use eZ\Publish\Core\Repository\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\Repository\SignalSlot\ContentTypeService;

class ContentTypeServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\ContentTypeService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new ContentTypeService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $userId = 14;
        $userVersionNo = 2;
        $contentTypeGroupId = 1;
        $contentTypeGroupIdentifier = 'Content';
        $contentTypeId = 42;
        $contentTypeIdentifier = 'ring';
        $contentTypeRemoteId = md5( 'The rings' );
        $copyContentTypeId = 43;
        $copyContentTypeIdentifier = 'ring2';
        $copyContentTypeRemoteId = md5( 'The rings 2' );
        $fieldDefinitionId = 242;
        $fieldDefinitionIdentifier = 'power';

        $contentTypeGroupCreateStruct = new ContentTypeGroupCreateStruct();
        $contentTypeGroupUpdateStruct = new ContentTypeGroupUpdateStruct();
        $contentTypeGroup = new ContentTypeGroup(
            array(
                'id' => $contentTypeGroupId,
                'identifier' => $contentTypeGroupIdentifier
            )
        );
        $contentTypeCreateStruct = new ContentTypeCreateStruct();
        $contentTypeUpdateStruct = new ContentTypeUpdateStruct();
        $contentType = new ContentType(
            array(
                'id' => $contentTypeId,
                'identifier' => $contentTypeIdentifier,
                'remoteId' => $contentTypeRemoteId,
                'fieldDefinitions' => array()
            )
        );
        $contentTypeDraft = new ContentTypeDraft(
            array(
                'innerContentType' => $contentType
            )
        );
        $copyContentType = new ContentType(
            array(
                'id' => $copyContentTypeId,
                'identifier' => $copyContentTypeIdentifier,
                'remoteId' => $copyContentTypeRemoteId,
                'fieldDefinitions' => array()
            )
        );
        $user = $this->getUser( $userId, md5( 'Sauron' ), $userVersionNo );

        $fieldDefinitionCreateStruct = new FieldDefinitionCreateStruct();
        $fieldDefinitionUpdateStruct = new FieldDefinitionUpdateStruct();
        $fieldDefinition = new FieldDefinition(
            array(
                'id' => $fieldDefinitionId,
                'identifier' => $fieldDefinitionIdentifier
            )
        );

        return array(
            array(
                'createContentTypeGroup',
                array( $contentTypeGroupCreateStruct ),
                $contentTypeGroup,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CreateContentTypeGroupSignal',
                array( 'groupId' => $contentTypeGroupId )
            ),
            array(
                'loadContentTypeGroup',
                array( $contentTypeGroupId ),
                $contentTypeGroup,
                0
            ),
            array(
                'loadContentTypeGroupByIdentifier',
                array( $contentTypeGroupIdentifier ),
                $contentTypeGroup,
                0
            ),
            array(
                'loadContentTypeGroups',
                array(),
                array( $contentTypeGroup ),
                0
            ),
            array(
                'updateContentTypeGroup',
                array( $contentTypeGroup, $contentTypeGroupUpdateStruct ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UpdateContentTypeGroupSignal',
                array( 'contentTypeGroupId' => $contentTypeGroupId )
            ),
            array(
                'deleteContentTypeGroup',
                array( $contentTypeGroup ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\DeleteContentTypeGroupSignal',
                array( 'contentTypeGroupId' => $contentTypeGroupId )
            ),
            array(
                'createContentType',
                array( $contentTypeCreateStruct, array( $contentTypeGroup ) ),
                $contentType,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CreateContentTypeSignal',
                array( 'contentTypeId' => $contentTypeId )
            ),
            array(
                'loadContentType',
                array( $contentTypeId ),
                array( $contentType ),
                0
            ),
            array(
                'loadContentTypeByIdentifier',
                array( $contentTypeIdentifier ),
                array( $contentType ),
                0
            ),
            array(
                'loadContentTypeByRemoteId',
                array( $contentTypeRemoteId ),
                array( $contentType ),
                0
            ),
            array(
                'loadContentTypeDraft',
                array( $contentType ),
                array( $contentTypeDraft ),
                0
            ),
            array(
                'loadContentTypes',
                array( $contentTypeGroup ),
                array( $contentType ),
                0
            ),
            array(
                'createContentTypeDraft',
                array( $contentType ),
                $contentTypeDraft,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CreateContentTypeDraftSignal',
                array( 'contentTypeId' => $contentTypeId )
            ),
            array(
                'updateContentTypeDraft',
                array( $contentTypeDraft, $contentTypeUpdateStruct ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UpdateContentTypeDraftSignal',
                array( 'contentTypeDraftId' => $contentTypeId )
            ),
            array(
                'deleteContentType',
                array( $contentType ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\DeleteContentTypeSignal',
                array( 'contentTypeId' => $contentTypeId )
            ),
            array(
                'copyContentType',
                array( $contentType, $user ),
                $copyContentType,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\CopyContentTypeSignal',
                array(
                    'contentTypeId' => $contentTypeId,
                    'userId' => $userId
                )
            ),
            array(
                'assignContentTypeGroup',
                array( $contentType, $contentTypeGroup ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\AssignContentTypeGroupSignal',
                array(
                    'contentTypeId' => $contentTypeId,
                    'contentTypeGroupId' => $contentTypeGroupId
                )
            ),
            array(
                'unassignContentTypeGroup',
                array( $contentType, $contentTypeGroup ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UnassignContentTypeGroupSignal',
                array(
                    'contentTypeId' => $contentTypeId,
                    'contentTypeGroupId' => $contentTypeGroupId
                )
            ),
            array(
                'addFieldDefinition',
                array( $contentTypeDraft, $fieldDefinitionCreateStruct ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\AddFieldDefinitionSignal',
                array(
                    'contentTypeDraftId' => $contentTypeId,
                )
            ),
            array(
                'removeFieldDefinition',
                array( $contentTypeDraft, $fieldDefinition ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\RemoveFieldDefinitionSignal',
                array(
                    'contentTypeDraftId' => $contentTypeId,
                    'fieldDefinitionId' => $fieldDefinitionId
                )
            ),
            array(
                'updateFieldDefinition',
                array( $contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\UpdateFieldDefinitionSignal',
                array(
                    'contentTypeDraftId' => $contentTypeId,
                    'fieldDefinitionId' => $fieldDefinitionId
                )
            ),
            array(
                'publishContentTypeDraft',
                array( $contentTypeDraft ),
                null,
                1,
                'eZ\Publish\Core\Repository\SignalSlot\Signal\ContentTypeService\PublishContentTypeDraftSignal',
                array(
                    'contentTypeDraftId' => $contentTypeId,
                )
            ),
            array(
                'newContentTypeGroupCreateStruct',
                array( 'Content' ),
                array( $contentTypeGroupCreateStruct ),
                0
            ),
            array(
                'newContentTypeCreateStruct',
                array( 'lords' ),
                array( $contentTypeCreateStruct ),
                0
            ),
            array(
                'newContentTypeUpdateStruct',
                array(),
                array( $contentTypeUpdateStruct ),
                0
            ),
            array(
                'newContentTypeGroupUpdateStruct',
                array(),
                array( $contentTypeGroupUpdateStruct ),
                0
            ),
            array(
                'newFieldDefinitionCreateStruct',
                array( 'ezstring', 'name' ),
                array( $fieldDefinitionCreateStruct ),
                0
            ),
            array(
                'newFieldDefinitionUpdateStruct',
                array(),
                array( $fieldDefinitionUpdateStruct ),
                0
            ),
        );
    }
}
