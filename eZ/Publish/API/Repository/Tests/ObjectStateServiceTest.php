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
use \eZ\Publish\API\Repository\Values\ObjectState\ObjectState;

use \eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for operations in the ObjectStateService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ObjectStateService
 * @group object-state
 */
class ObjectStateServiceTest extends BaseTest
{
    /**
     * Test for the newObjectStateGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetObjectStateService
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetObjectStateService
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetObjectStateService
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetObjectStateService
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetObjectStateService
     * @depends testNewObjectStateGroupCreateStructValues
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
     * Test for the createObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup()
     * @depends testCreateObjectStateGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateObjectStateGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateService = $repository->getObjectStateService();

        $objectStateGroupCreate = $objectStateService->newObjectStateGroupCreateStruct(
            // 'ez_lock' is already existing identifier
            'ez_lock'
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

        // This call will fail because group with 'ez_lock' identifier already exists
        $objectStateService->createObjectStateGroup(
            $objectStateGroupCreate
        );
    }

    /**
     * Test for the loadObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetObjectStateService
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
     * @depends testLoadObjectStateGroup
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
     * @depends testLoadObjectStateGroup
     */
    public function testLoadObjectStateGroups()
    {
        $repository = $this->getRepository();

        $expectedGroupIdentifiers = $this->getGroupIdentifierMap( $this->createObjectStateGroups() );
        $expectedGroupIdentifiers['ez_lock'] = true;

        /* BEGIN: Use Case */
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroups = $objectStateService->loadObjectStateGroups();
        /* END: Use Case */

        $this->assertInternalType( 'array', $loadedObjectStateGroups );

        $this->assertObjectsLoadedByIdentifiers(
            $expectedGroupIdentifiers,
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
            'first',
            'second',
            'third',
        );

        $createdStateGroups = array();

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct( 'dummy' );

        $groupCreateStruct->defaultLanguageCode = 'eng-US';
        $groupCreateStruct->names               = array( 'eng-US' => 'Foo' );
        $groupCreateStruct->descriptions        = array( 'eng-US' => 'Foo Bar' );

        foreach ( $identifiersToCreate as $identifier )
        {
            $groupCreateStruct->identifier = $identifier;
            $createdStateGroups[] = $objectStateService->createObjectStateGroup( $groupCreateStruct );
        }

        return $createdStateGroups;
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

        if ( !empty( $expectedIdentifiers ) )
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
     * @depends testLoadObjectStateGroups
     */
    public function testLoadObjectStateGroupsWithOffset()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $this->createObjectStateGroups();

        $allObjectStateGroups = $objectStateService->loadObjectStateGroups();

        $existingGroupIdentifiers = $this->getGroupIdentifierMap( $allObjectStateGroups );

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
     * Returns a map of the given object state groups
     *
     * @param array $groups
     * @return void
     */
    protected function getGroupIdentifierMap( array $groups )
    {
        $existingGroupIdentifiers = array_map(
            function ( $group )
            {
                return $group->identifier;
            },
            $groups
        );

        return array_fill_keys( $existingGroupIdentifiers, true );
    }

    /**
     * Test for the loadObjectStateGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups($offset, $limit)
     * @depends testLoadObjectStateGroupsWithOffset
     */
    public function testLoadObjectStateGroupsWithOffsetAndLimit()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $allObjectStateGroups = $objectStateService->loadObjectStateGroups();

        $existingGroupIdentifiers = $this->getGroupIdentifierMap( $allObjectStateGroups );

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
     * @depends testLoadObjectStateGroup
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
     * @depends testLoadObjectStateGroup
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
     * Test for the updateObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup()
     * @depends testUpdateObjectStateGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateObjectStateGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateService = $repository->getObjectStateService();

        // Create object state group which we will later update
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

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        // 'ez_lock' is the identifier of already existing group
        $groupUpdateStruct->identifier = 'ez_lock';
        $groupUpdateStruct->defaultLanguageCode = 'ger-DE';
        $groupUpdateStruct->names = array(
            'ger-DE' => 'Sindelfingen',
        );
        $groupUpdateStruct->descriptions = array(
            'ger-DE' => 'Sindelfingen ist nicht nur eine Stadt'
        );

        // This call will fail since state group with 'ez_lock' identifier already exists
        $objectStateService->updateObjectStateGroup(
            $createdObjectStateGroup,
            $groupUpdateStruct
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
     * @depends testLoadObjectStateGroup
     * @depends testNewObjectStateCreateStruct
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
        // Object sequences are renumbered
        $objectStateCreateStruct->priority = 2;
        return array(
            $loadedObjectStateGroup,
            $objectStateCreateStruct,
            $createdObjectState
        );
    }

    /**
     * Test for the createObjectState() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::createObjectState()
     * @depends testLoadObjectStateGroup
     * @depends testCreateObjectState
     */
    public function testCreateObjectStateThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        // $objectStateGroupId contains the ID of the standard object state
        // group ez_lock.
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $objectStateGroupId
        );

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            // 'not_locked' is the identifier of already existing state
            'not_locked'
        );
        $objectStateCreateStruct->priority = 23;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateCreateStruct->names = array(
            'eng-US' => 'Locked and Unlocked',
        );
        $objectStateCreateStruct->descriptions = array(
            'eng-US' => 'A state between locked and unlocked.',
        );

        // This call will fail because object state with
        // 'not_locked' identifier already exists
        $objectStateService->createObjectState(
            $loadedObjectStateGroup,
            $objectStateCreateStruct
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
     * @depends testLoadObjectStateGroup
     */
    public function testLoadObjectState()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $loadedObjectState
        );

        return $loadedObjectState;
    }

    /**
     * testLoadObjectStateStructValues
     *
     * @param ObjectState $loadedObjectState
     * @return void
     * @depends testLoadObjectState
     */
    public function testLoadObjectStateStructValues( ObjectState $loadedObjectState )
    {
        $this->assertPropertiesCorrect(
            array(
                'id'                  => 2,
                'identifier'          => 'locked',
                'priority'            => 1,
                'defaultLanguageCode' => 'eng-US',
                'languageCodes'       => array( 0 => 'eng-US' ),
                'names'               => array( 'eng-US' => 'Locked' ),
                'descriptions'        => array( 'eng-US' => '' ),
            ),
            $loadedObjectState
        );

        $this->assertEquals(
            $this->getRepository()->getObjectStateService()->loadObjectStateGroup( 2 ),
            $loadedObjectState->getObjectStateGroup()
        );
    }

    /**
     * Test for the loadObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::loadObjectState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends testLoadObjectState
     */
    public function testLoadObjectStateThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingObjectStateId = $this->generateId( 'objectstate', PHP_INT_MAX );
        /* BEGIN: Use Case */
        // $nonExistingObjectStateId contains the ID of a non existing state
        $objectStateService = $repository->getObjectStateService();

        // Throws not found exception
        $loadedObjectState = $objectStateService->loadObjectState(
            $nonExistingObjectStateId
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectState()
     * @depends testLoadObjectState
     */
    public function testUpdateObjectState()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        $updateStateStruct = $objectStateService->newObjectStateUpdateStruct();
        $updateStateStruct->identifier = 'somehow_locked';
        $updateStateStruct->defaultLanguageCode = 'ger-DE';
        $updateStateStruct->names = array(
            'eng-US' => 'Somehow locked',
            'ger-DE' => 'Irgendwie gelockt',
        );
        $updateStateStruct->descriptions = array(
            'eng-US' => 'The object is somehow locked',
            'ger-DE' => 'Sindelfingen',
        );

        $updatedObjectState = $objectStateService->updateObjectState(
            $loadedObjectState,
            $updateStateStruct
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $updatedObjectState
        );

        return array(
            $loadedObjectState,
            $updateStateStruct,
            $updatedObjectState
        );
    }

    /**
     * Test for the updateObjectState() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::updateObjectState()
     * @depends testUpdateObjectState
     */
    public function testUpdateObjectStateThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId( 'objectstate', 2 );
        // $objectStateId contains the ID of the "locked" state
        $objectStateService = $repository->getObjectStateService();

        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        $updateStateStruct = $objectStateService->newObjectStateUpdateStruct();
        // 'not_locked' is the identifier of already existing state
        $updateStateStruct->identifier = 'not_locked';
        $updateStateStruct->defaultLanguageCode = 'ger-DE';
        $updateStateStruct->names = array(
            'eng-US' => 'Somehow locked',
            'ger-DE' => 'Irgendwie gelockt',
        );
        $updateStateStruct->descriptions = array(
            'eng-US' => 'The object is somehow locked',
            'ger-DE' => 'Sindelfingen',
        );

        // This call will fail because state with
        // 'not_locked' identifier already exists
        $objectStateService->updateObjectState(
            $loadedObjectState,
            $updateStateStruct
        );
    }

    /**
     * testUpdateObjectStateStructValues
     *
     * @param array $testData
     * @return void
     * @depends testUpdateObjectState
     */
    public function testUpdateObjectStateStructValues( array $testData )
    {
        list(
            $loadedObjectState,
            $updateStateStruct,
            $updatedObjectState
        ) = $testData;

        $this->assertPropertiesCorrect(
            array(
                'id'                  => $loadedObjectState->id,
                'identifier'          => $updateStateStruct->identifier,
                'priority'            => $loadedObjectState->priority,
                'defaultLanguageCode' => $updateStateStruct->defaultLanguageCode,
                'languageCodes'       => array( 'eng-US', 'ger-DE' ),
                'names'               => $updateStateStruct->names,
                'descriptions'        => $updateStateStruct->descriptions,
            ),
            $updatedObjectState
        );

        $this->assertEquals(
            $loadedObjectState->getObjectStateGroup(),
            $updatedObjectState->getObjectStateGroup()
        );
    }

    /**
     * Test for the setPriorityOfObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setPriorityOfObjectState()
     * @depends testLoadObjectState
     */
    public function testSetPriorityOfObjectState()
    {
        $repository = $this->getRepository();

        $objectStateId = $this->generateId( 'objectstate', 1 );
        /* BEGIN: Use Case */
        // $objectStateId contains the ID of the "not_locked" state
        $objectStateService = $repository->getObjectStateService();

        $initiallyLoadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );

        // Sets the given priority on $initiallyLoadedObjectState
        $objectStateService->setPriorityOfObjectState(
            $initiallyLoadedObjectState,
            23
        );
        // $loadObjectState now has the priority 1, since object state
        // priorities are always made sequential
        $loadedObjectState = $objectStateService->loadObjectState(
            $objectStateId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $loadedObjectState
        );
        $this->assertEquals( 1, $loadedObjectState->priority );
    }

    /**
     * Test for the getContentState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::getContentState()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends testLoadObjectState
     */
    public function testGetContentState()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        $ezLockObjectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        $contentService     = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $ezLockObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $ezLockObjectStateGroupId
        );

        // Loads the state of $contentInfo in the "ez_lock" object state group
        $ezLockObjectState = $objectStateService->getContentState(
            $contentInfo,
            $ezLockObjectStateGroup
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $ezLockObjectState
        );
        $this->assertEquals( 'not_locked', $ezLockObjectState->identifier );
    }

    /**
     * testGetInitialObjectState
     *
     * @return void
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends testLoadObjectState
     */
    public function testGetInitialObjectState()
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        // Create object state group with custom state
        $createdStateGroups = $this->createObjectStateGroups();

        $customObjectStateGroupId = $createdStateGroups[1]->id;
        $anonymousUserId = $this->generateId( 'user', 10 );

        $customGroup = $objectStateService->loadObjectStateGroup(
            $customObjectStateGroupId
        );

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct(
            'sindelfingen'
        );
        $objectStateCreateStruct->priority = 1;
        $objectStateCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateCreateStruct->names = array( 'eng-US' => 'Sindelfingen' );

        $createdState = $objectStateService->createObjectState(
            $customGroup,
            $objectStateCreateStruct
        );

        // Store state ID to be used
        $customObjectStateId = $createdState->id;

        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        // $customObjectStateGroupId is the ID of a state group, from which no
        // state has been assigned to $anonymousUserId, yet
        $contentService     = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $customObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $customObjectStateGroupId
        );

        // Loads the initial state of the custom state group
        $initialObjectState = $objectStateService->getContentState(
            $contentInfo,
            $customObjectStateGroup
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $initialObjectState
        );
        $this->assertEquals( 'sindelfingen', $initialObjectState->identifier );
        $this->assertEquals( array( 'eng-US' => 'Sindelfingen' ), $initialObjectState->names );
        $this->assertEquals( 'eng-US', $initialObjectState->defaultLanguageCode );
    }

    /**
     * Test for the setContentState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setContentState()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends testLoadObjectState
     */
    public function testSetContentState()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId( 'user', 10 );
        $ezLockObjectStateGroupId = $this->generateId( 'objectstategroup', 2 );
        $lockedObjectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        // $ezLockObjectStateGroupId contains the ID of the "ez_lock" object
        // state group
        // $lockedObjectStateId is the ID of the state "locked"
        $contentService     = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $ezLockObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $ezLockObjectStateGroupId
        );
        $lockedObjectState = $objectStateService->loadObjectState( $lockedObjectStateId );

        // Sets the state of $contentInfo from "not_locked" to "locked"
        $objectStateService->setContentState(
            $contentInfo,
            $ezLockObjectStateGroup,
            $lockedObjectState
        );
        /* END: Use Case */

        $ezLockObjectState = $objectStateService->getContentState(
            $contentInfo,
            $ezLockObjectStateGroup
        );

        $this->assertEquals( 'locked', $ezLockObjectState->identifier );
    }

    /**
     * Test for the setContentState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::setContentState()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends testSetContentState
     */
    public function testSetContentStateThrowsInvalidArgumentExceptioon()
    {
        $repository = $this->getRepository();

        $createdStateGroups = $this->createObjectStateGroups();

        $anonymousUserId = $this->generateId( 'user', 10 );
        $differentObjectStateGroupId = $createdStateGroups[1]->id;
        $lockedObjectStateId = $this->generateId( 'objectstate', 2 );

        /* BEGIN: Use Case */
        // $anonymousUserId is the content ID of "Anonymous User"
        // $differentObjectStateGroupId contains the ID of an object state
        // group which does not contain $lockedObjectStateId
        // $lockedObjectStateId is the ID of the state "locked"
        $contentService     = $repository->getContentService();
        $objectStateService = $repository->getObjectStateService();

        $contentInfo = $contentService->loadContentInfo( $anonymousUserId );

        $differentObjectStateGroup = $objectStateService->loadObjectStateGroup(
            $differentObjectStateGroupId
        );
        $lockedObjectState = $objectStateService->loadObjectState( $lockedObjectStateId );

        // Throws an invalid argument exception since $lockedObjectState does
        // not belong to $differentObjectStateGroup
        $objectStateService->setContentState(
            $contentInfo,
            $differentObjectStateGroup,
            $lockedObjectState
        );
        /* END: Use Case */
    }

    /**
     * Test for the getContentCount() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::getContentCount()
     * @depens testLoadObjectState
     */
    public function testGetContentCount()
    {
        $repository = $this->getRepository();

        $notLockedObjectStateId = $this->generateId( 'objectstate', 1 );
        /* BEGIN: Use Case */
        // $notLockedObjectStateId is the ID of the state "not_locked"
        $objectStateService = $repository->getObjectStateService();

        $notLockedObjectState = $objectStateService->loadObjectState( $notLockedObjectStateId );

        $objectCount = $objectStateService->getContentCount( $notLockedObjectState );
        /* END: Use Case */

        $this->assertEquals( 18, $objectCount );
    }

    /**
     * Test for the deleteObjectState() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectState()
     * @depens testLoadObjectState
     */
    public function testDeleteObjectState()
    {
        $repository = $this->getRepository();

        $notLockedObjectStateId = $this->generateId( 'objectstate', 1 );
        $lockedObjectStateId = $this->generateId( 'objectstate', 2 );
        /* BEGIN: Use Case */
        // $notLockedObjectStateId is the ID of the state "not_locked"
        $objectStateService = $repository->getObjectStateService();

        $notLockedObjectState = $objectStateService->loadObjectState( $notLockedObjectStateId );

        // Deletes the object state and sets all objects, which where in that
        // state, to the first state of the same object state group
        $objectStateService->deleteObjectState( $notLockedObjectState );
        /* END: Use Case */

        $lockedObjectState = $objectStateService->loadObjectState( $lockedObjectStateId );

        // All objects transfered
        $this->assertEquals(
            18,
            $objectStateService->getContentCount( $lockedObjectState )
        );
    }

    /**
     * Test for the deleteObjectStateGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ObjectStateService::deleteObjectStateGroup()
     * @depens testLoadObjectStateGroup
     */
    public function testDeleteObjectStateGroup()
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

        $objectStateService->deleteObjectStateGroup( $loadedObjectStateGroup );
        /* END: Use Case */

        try
        {
            $objectStateService->loadObjectStateGroup( $objectStateGroupId );
            $this->fail(
                sprintf(
                    'ObjectStateGroup with ID "%s" not deleted.',
                    $objectStateGroupId
                )
            );
        }
        catch ( NotFoundException $e ) {}
    }
}
