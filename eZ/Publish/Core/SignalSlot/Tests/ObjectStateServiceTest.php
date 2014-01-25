<?php
/**
 * File containing the ObjectStateTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\ObjectState\ObjectState;
use eZ\Publish\Core\Repository\DomainLogic\Values\ObjectState\ObjectStateGroup;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\ObjectStateService;

class ObjectStateServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\ObjectStateService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new ObjectStateService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $objectStateGroupId = 4;
        $objectStateId = 42;
        $priority = 50;
        $contentId = 59;
        $contentRemoteId = md5( "What's up doc ?" );

        $objectStateGroupCreateStruct = new ObjectStateGroupCreateStruct();
        $objectStateGroupUpdateStruct = new ObjectStateGroupUpdateStruct();
        $objectStateCreateStruct = new ObjectStateCreateStruct();
        $objectStateUpdateStruct = new ObjectStateUpdateStruct();
        $objectStateGroup = new ObjectStateGroup(
            array(
                'id' => $objectStateGroupId
            )
        );
        $objectState = new ObjectState(
            array(
                'id' => $objectStateId
            )
        );
        $contentInfo = $this->getContentInfo( $contentId, $contentRemoteId );

        return array(
            array(
                'createObjectStateGroup',
                array( $objectStateGroupCreateStruct ),
                $objectStateGroup,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\CreateObjectStateGroupSignal',
                array( 'objectStateGroupId' => $objectStateGroupId )
            ),
            array(
                'loadObjectStateGroup',
                array( 4 ),
                $objectStateGroup,
                0,
            ),
            array(
                'loadObjectStateGroups',
                array( 1, 1 ),
                array( $objectStateGroup ),
                0,
            ),
            array(
                'loadObjectStates',
                array( $objectStateGroup ),
                array( $objectState ),
                0,
            ),
            array(
                'updateObjectStateGroup',
                array( $objectStateGroup, $objectStateGroupUpdateStruct ),
                $objectStateGroup,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\UpdateObjectStateGroupSignal',
                array( 'objectStateGroupId' => $objectStateGroupId )
            ),
            array(
                'deleteObjectStateGroup',
                array( $objectStateGroup ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\DeleteObjectStateGroupSignal',
                array( 'objectStateGroupId' => $objectStateGroupId )
            ),
            array(
                'createObjectState',
                array( $objectStateGroup, $objectStateCreateStruct ),
                $objectState,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\CreateObjectStateSignal',
                array(
                    'objectStateGroupId' => $objectStateGroupId,
                    'objectStateId' => $objectStateId
                )
            ),
            array(
                'loadObjectState',
                array( $objectStateId ),
                $objectState,
                0
            ),
            array(
                'updateObjectState',
                array( $objectState, $objectStateUpdateStruct ),
                $objectState,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\UpdateObjectStateSignal',
                array(
                    'objectStateId' => $objectStateId
                )
            ),
            array(
                'setPriorityOfObjectState',
                array( $objectState, $priority ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetPriorityOfObjectStateSignal',
                array(
                    'objectStateId' => $objectStateId,
                    'priority' => $priority
                )
            ),
            array(
                'deleteObjectState',
                array( $objectState ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\DeleteObjectStateSignal',
                array(
                    'objectStateId' => $objectStateId
                )
            ),
            array(
                'setContentState',
                array( $contentInfo, $objectStateGroup, $objectState ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetContentStateSignal',
                array(
                    'objectStateId' => $objectStateId,
                    'contentId' => $contentId,
                    'objectStateGroupId' => $objectStateGroupId,
                )
            ),
            array(
                'getContentState',
                array( $contentInfo, $objectStateGroup ),
                $objectState,
                0,
            ),
            array(
                'getContentCount',
                array( $objectState ),
                35,
                0
            ),
            array(
                'newObjectStateGroupCreateStruct',
                array( 'identifier' ),
                $objectStateGroupCreateStruct,
                0
            ),
            array(
                'newObjectStateGroupUpdateStruct',
                array(),
                $objectStateGroupUpdateStruct,
                0
            ),
            array(
                'newObjectStateUpdateStruct',
                array(),
                $objectStateUpdateStruct,
                0
            ),
            array(
                'newObjectStateCreateStruct',
                array( 'identifier' ),
                $objectStateCreateStruct,
                0
            ),
        );
    }
}
