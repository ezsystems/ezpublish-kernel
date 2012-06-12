<?php
/**
 * File containing the ObjectStateServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;

/**
 * Test case for operations in the ObjectStateService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ObjectStateService
 */
class ObjectStateServiceTest extends \eZ\Publish\API\Repository\Tests\BaseTest
{
    /**
     * Test for the newObjectStateGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupCreateStruct()
     *
     */
    public function testNewObjectStateGroupCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupCreate = $objectStateService->newObjectStateGroupCreateStruct(
            'publishing'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupCreateStruct',
            $objectStateGroupCreate
        );
        return $objectStateGroupCreate;
    }

    /**
     * testNewObjectStateGroupCreateStructValues
     *
     * @param ObjectStateGroupCreateStruct $objectStateGroupCreate
     * @return void
     * @depends testNewObjectStateGroupCreateStruct
     */
    public function testNewObjectStateGroupCreateStructValues( ObjectStateGroupCreateStruct $objectStateGroupCreate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => 'publishing',
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateGroupCreate
        );
    }

    /**
     * Test for the newObjectStateGroupUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupUpdateStruct()
     *
     */
    public function testNewObjectStateGroupUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupUpdate = $objectStateService->newObjectStateGroupUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupUpdateStruct',
            $objectStateGroupUpdate
        );
        return $objectStateGroupUpdate;
    }

    /**
     * testNewObjectStateGroupUpdateStructValues
     *
     * @param ObjectStateGroupUpdateStruct $objectStateGroupUpdate
     * @return void
     * @depends testNewObjectStateGroupUpdateStruct
     */
    public function testNewObjectStateGroupUpdateStructValues( ObjectStateGroupUpdateStruct $objectStateGroupUpdate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => null,
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateGroupUpdate
        );
    }

    /**
     * Test for the newObjectStateCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateCreateStruct()
     *
     */
    public function testNewObjectStateCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateCreate = $objectStateService->newObjectStateCreateStruct(
            'pending'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateCreateStruct',
            $objectStateCreate
        );
        return $objectStateCreate;
    }

    /**
     * testNewObjectStateCreateStructValues
     *
     * @param ObjectStateCreateStruct $objectStateCreate
     * @return void
     * @depends testNewObjectStateCreateStruct
     */
    public function testNewObjectStateCreateStructValues( ObjectStateCreateStruct $objectStateCreate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => 'pending',
                'priority'            => false,
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateCreate
        );
    }

    /**
     * Test for the newObjectStateUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateUpdateStruct()
     *
     */
    public function testNewObjectStateUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateUpdate = $objectStateService->newObjectStateUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateUpdateStruct',
            $objectStateUpdate
        );
        return $objectStateUpdate;
    }

    /**
     * testNewObjectStateUpdateStructValues
     *
     * @param ObjectStateUpdateStruct $objectStateUpdate
     * @return void
     * @depends testNewObjectStateUpdateStruct
     */
    public function testNewObjectStateUpdateStructValues( ObjectStateUpdateStruct $objectStateUpdate )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => null,
                'defaultLanguageCode' => null,
                'names'               => null,
                'descriptions'        => null,
            ),
            $objectStateUpdate
        );
    }

    /**
     * Test for the createObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup()
     *
     */
    public function testCreateObjectStateGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupCreate = $objectStateService->newObjectStateGroupCreateStruct(
            'publishing'
        );
        $objectStateGroupCreate->defaultLanguageCode = 'eng-US';
        $objectStateGroupCreate->names = array(
            'eng-US' => 'Publishing',
            'eng-GB' => 'Sindelfingen',
        );
        $objectStateGroupCreate->descriptions = array(
            'eng-US' => 'Put something online',
            'eng-GB' => 'Put something ton Sindelfingen.',
        );

        $createdObjectStateGroup = $objectStateService->createObjectStateGroup(
            $objectStateGroupCreate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $createdObjectStateGroup
        );
        return $createdObjectStateGroup;
    }

    /**
     * testCreateObjectStateGroupStructValues
     *
     * @param ObjectStateGroup $createdObjectStateGroup
     * @return void
     * @depends testCreateObjectStateGroup
     */
    public function testCreateObjectStateGroupStructValues( ObjectStateGroup $createdObjectStateGroup )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'          => 'publishing',
                'defaultLanguageCode' => 'eng-US',
                'languageCodes'       => array( 'eng-US', 'eng-GB' ),
                'names'               => array(
                    'eng-US' => 'Publishing',
                    'eng-GB' => 'Sindelfingen',
                ),
                'descriptions'        => array(
                    'eng-US' => 'Put something online',
                    'eng-GB' => 'Put something ton Sindelfingen.',
                ),
            ),
            $createdObjectStateGroup
        );
        $this->assertNotNull( $createdObjectStateGroup->id );
    }

    /**
     * Test for the loadObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup()
     *
     */
    public function testLoadObjectStateGroup()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $loadedObjectStateGroup
        );
        return $loadedObjectStateGroup;
    }

    /**
     * Test for the loadObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadObjectStateGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentObjectStateGroupId = $this->generateId( 'objectstategroup', PHP_INT_MAX );
        /* BEGIN: Use Case */
        // $nonExistentObjectStateGroupId contains an ID for an object state
        // that does not exist
        $objectStateService = $repository->getObjectStateService();

        // Throws a not found exception
        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $nonExistentObjectStateGroupId
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups()
     *
     */
    public function testLoadObjectStateGroups()
    {
        $repository = $this->getRepository();

        $existingGroupIdentifiers = $this->createObjectStateGroups();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroups = $objectStateService->loadObjectStateGroups();
        /* END: Use Case */

        $this->assertInternalType( 'array', $loadedObjectStateGroups );

        $this->assertObjectsLoadedByIdentifiers(
            $existingGroupIdentifiers,
            $loadedObjectStateGroups,
            'ObjectStateGroup'
        );
    }

    /**
     * Creates a set of object state groups and returns an array of all
     * existing group identifiers after creation
     *
     * @return bool[]
     */
    protected function createObjectStateGroups()
    {
        $repository         = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $identifiersToCreate = array(
            'first'  => true,
            'second' => true,
            'third'  => true,
        );

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct( 'dummy' );

        $groupCreateStruct->defaultLanguageCode = 'eng-US';
        $groupCreateStruct->names               = array( 'eng-US' => 'Foo' );
        $groupCreateStruct->descriptions        = array( 'eng-US' => 'Foo Bar' );

        foreach ( array_keys( $identifiersToCreate ) as $identifier )
        {
            $groupCreateStruct->identifier = $identifier;
            $objectStateService->createObjectStateGroup( $groupCreateStruct );
        }

        return array_merge(
            array( 'ez_lock' => true ),
            $identifiersToCreate
        );
    }

    /**
     * testLoadObjectStateGroupsLoadedExpectedGroups
     *
     * @param array $loadObjectStateGroups
     * @depends testLoadObjectStateGroups
     */
    protected function assertObjectsLoadedByIdentifiers( array $expectedIdentifiers, array $loadedObjects, $class )
    {
        foreach ( $loadedObjects as $loadedObject )
        {
            if ( !isset( $expectedIdentifiers[$loadedObject->identifier] ) )
            {
                $this->fail(
                    sprintf(
                        'Loaded not expected %s with identifier "%s"',
                        $class,
                        $loadedObject->identifier
                    )
                );
            }
            unset( $expectedIdentifiers[$loadedObject->identifier] );
        }

        if ( count( $expectedIdentifiers ) !== 0 )
        {
            $this->fail(
                sprintf(
                    'Expected %ss with identifiers "%s" not loaded.',
                    $class,
                    implode( '", "', $expectedIdentifiers )
                )
            );
        }
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups($offset)
     *
     */
    public function testLoadObjectStateGroupsWithOffset()
    {
        $repository = $this->getRepository();

        $existingGroupIdentifiers = $this->createObjectStateGroups();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroups = $objectStateService->loadObjectStateGroups( 2 );
        /* END: Use Case */

        $this->assertInternalType( 'array', $loadedObjectStateGroups );

        $this->assertObjectsLoadedByIdentifiers(
            array_slice( $existingGroupIdentifiers, 2 ),
            $loadedObjectStateGroups,
            'ObjectStateGroup'
        );
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups($offset, $limit)
     *
     */
    public function testLoadObjectStateGroupsWithOffsetAndLimit()
    {
        $repository = $this->getRepository();

        $existingGroupIdentifiers = $this->createObjectStateGroups();

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroups = $objectStateService->loadObjectStateGroups( 1, 2 );
        /* END: Use Case */

        $this->assertInternalType( 'array', $loadedObjectStateGroups );

        $this->assertObjectsLoadedByIdentifiers(
            array_slice( $existingGroupIdentifiers, 1, 2 ),
            $loadedObjectStateGroups,
            'ObjectStateGroup'
        );
    }

    /**
     * Test for the loadObjectStates() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStates()
     *
     */
    public function testLoadObjectStates()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        // Loads all object states in $objectStateGroup
        $loadedObjectStates = $objectStateService->loadObjectStates( $objectStateGroup );
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedObjectStates
        );
        $this->assertObjectsLoadedByIdentifiers(
            array( 'not_locked' => true, 'locked' => true ),
            $loadedObjectStates,
            'ObjectState'
        );
    }

    /**
     * Test for the updateObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup()
     *
     */
    public function testUpdateObjectStateGroup()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        $groupUpdateStruct->identifier = 'sindelfingen';
        $groupUpdateStruct->defaultLanguageCode = 'ger-DE';
        $groupUpdateStruct->names = array(
            'ger-DE' => 'Sindelfingen',
        );
        $groupUpdateStruct->descriptions = array(
            'ger-DE' => 'Sindelfingen ist nicht nur eine Stadt'
        );

        // Updates the $loadObjectStateGroup with the data from
        // $groupUpdateStruct and returns the updated group
        $updatedObjectStateGroup = $objectStateService->updateObjectStateGroup(
            $loadedObjectStateGroup,
            $groupUpdateStruct
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $updatedObjectStateGroup
        );
        return array(
            $loadedObjectStateGroup,
            $groupUpdateStruct,
            $updatedObjectStateGroup
        );
    }

    /**
     * testUpdateObjectStateGroupStructValues
     *
     * @param array $testData
     * @return void
     * @depends testUpdateObjectStateGroup
     */
    public function testUpdateObjectStateGroupStructValues( array $testData )
    {
        list(
            $loadedObjectStateGroup,
            $groupUpdateStruct,
            $updatedObjectStateGroup
        ) = $testData;

        $this->assertStructPropertiesCorrect(
            $groupUpdateStruct,
            $updatedObjectStateGroup
        );
    }

    /**
     * Test for the createObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectState()
     *
     */
    public function testCreateObjectState()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            'locked_and_unlocked'
        );
        $objectStateCreateStruct->priority = 23;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateCreateStruct->names = array(
            'eng-US' => 'Locked and Unlocked',
        );
        $objectStateCreateStruct->descriptions = array(
            'eng-US' => 'A state between locked and unlocked.',
        );

        // Creates a new object state in the $loadObjectStateGroup with the
        // data from $objectStateCreateStruct
        $createdObjectState = $objectStateService->createObjectState(
            $loadedObjectStateGroup,
            $objectStateCreateStruct
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $createdObjectState
        );
        return array(
            $loadedObjectStateGroup,
            $objectStateCreateStruct,
            $createdObjectState
        );
    }

    /**
     * testCreateObjectStateStructValues
     *
     * @param array $testData
     * @return void
     * @depends testCreateObjectState
     */
    public function testCreateObjectStateStructValues( array $testData )
    {
        list(
            $loadedObjectStateGroup,
            $objectStateCreateStruct,
            $createdObjectState
        ) = $testData;

        $this->assertStructPropertiesCorrect(
            $objectStateCreateStruct,
            $createdObjectState
        );

        $this->assertNotNull( $createdObjectState->id );

        $this->assertEquals(
            $loadedObjectStateGroup,
            $createdObjectState->getObjectStateGroup()
        );
    }

    /**
     * Test for the loadObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectState()
     *
     */
    public function testLoadObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectState() is not implemented." );
    }

    /**
     * Test for the loadObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadObjectStateThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::loadObjectState() is not implemented." );
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectState()
     *
     */
    public function testUpdateObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::updateObjectState() is not implemented." );
    }

    /**
     * Test for the setPriorityOfObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setPriorityOfObjectState()
     *
     */
    public function testSetPriorityOfObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::setPriorityOfObjectState() is not implemented." );
    }

    /**
     * Test for the deleteObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectState()
     *
     */
    public function testDeleteObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::deleteObjectState() is not implemented." );
    }

    /**
     * Test for the setObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setObjectState()
     *
     */
    public function testSetObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::setObjectState() is not implemented." );
    }

    /**
     * Test for the setObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentExceptioon
     */
    public function testSetObjectStateThrowsInvalidArgumentExceptioon()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::setObjectState() is not implemented." );
    }

    /**
     * Test for the getObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::getObjectState()
     *
     */
    public function testGetObjectState()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::getObjectState() is not implemented." );
    }

    /**
     * Test for the getContentCount() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::getContentCount()
     *
     */
    public function testGetContentCount()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::getContentCount() is not implemented." );
    }

    /**
     * Test for the deleteObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectStateGroup()
     *
     */
    public function testDeleteObjectStateGroup()
    {
        $this->markTestIncomplete( "Test for ObjectStateService::deleteObjectStateGroup() is not implemented." );
    }
}
