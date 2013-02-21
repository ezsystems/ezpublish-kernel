<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\ObjectStateBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for object state Service
 */
abstract class ObjectStateBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__construct
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__construct
     */
    public function testNewClass()
    {
        $objectState = new ObjectState();

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'identifier' => null,
                'priority' => null,
                'defaultLanguageCode' => null,
                'languageCodes' => null,
                'names' => array(),
                'descriptions' => array()
            ),
            $objectState
        );

        $objectStateGroup = new ObjectStateGroup();

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'identifier' => null,
                'defaultLanguageCode' => null,
                'languageCodes' => null,
                'names' => array(),
                'descriptions' => array()
            ),
            $objectStateGroup
        );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__get
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $objectState = new ObjectState();
            $value = $objectState->notDefined;
            $this->fail( "Succeeded getting non existing property" );
        }
        catch ( PropertyNotFound $e )
        {
        }

        try
        {
            $objectStateGroup = new ObjectStateGroup();
            $value = $objectStateGroup->notDefined;
            $this->fail( "Succeeded getting non existing property" );
        }
        catch ( PropertyNotFound $e )
        {
        }
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__set
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $objectState = new ObjectState();
            $objectState->id = 42;
            $this->fail( "Succeeded setting read only property" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }

        try
        {
            $objectStateGroup = new ObjectStateGroup();
            $objectStateGroup->id = 42;
            $this->fail( "Succeeded setting read only property" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__isset
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__isset
     */
    public function testIsPropertySet()
    {
        $objectState = new ObjectState();
        $value = isset( $objectState->notDefined );
        $this->assertEquals( false, $value );

        $value = isset( $objectState->id );
        $this->assertEquals( true, $value );

        $objectStateGroup = new ObjectStateGroup();
        $value = isset( $objectStateGroup->notDefined );
        $this->assertEquals( false, $value );

        $value = isset( $objectStateGroup->id );
        $this->assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectState::__unset
     * @covers \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup::__unset
     */
    public function testUnsetProperty()
    {
        $objectState = new ObjectState( array( "id" => 2 ) );
        try
        {
            unset( $objectState->id );
            $this->fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }

        $objectStateGroup = new ObjectStateGroup( array( "id" => 2 ) );
        try
        {
            unset( $objectStateGroup->id );
            $this->fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test service method for creating object state group
     * @covers \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup
     */
    public function testCreateGroup()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct( 'test' );
        $groupCreateStruct->defaultLanguageCode = 'eng-GB';
        $groupCreateStruct->names = array( 'eng-GB' => 'Test' );
        $groupCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $createdGroup = $objectStateService->createObjectStateGroup( $groupCreateStruct );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $createdGroup
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 3,
                'identifier' => 'test',
                'defaultLanguageCode' => 'eng-GB',
                'languageCodes' => array( 'eng-GB' ),
                'names' => array( 'eng-GB' => 'Test' ),
                'descriptions' => array( 'eng-GB' => 'Test description' )
            ),
            $createdGroup
        );
    }

    /**
     * Test service method for creating object state group throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\ObjectStateService::createObjectStateGroup
     */
    public function testCreateGroupThrowsInvalidArgumentException()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct( 'ez_lock' );
        $groupCreateStruct->defaultLanguageCode = 'eng-GB';
        $groupCreateStruct->names = array( 'eng-GB' => 'Test' );
        $groupCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $objectStateService->createObjectStateGroup( $groupCreateStruct );
    }

    /**
     * Test service method for loading object state group
     * @covers \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup
     */
    public function testLoadObjectStateGroup()
    {
        $group = $this->repository->getObjectStateService()->loadObjectStateGroup( 2 );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $group
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 2,
                'identifier' => 'ez_lock',
                'defaultLanguageCode' => 'eng-US',
                'languageCodes' => array( 'eng-US' ),
                'names' => array( 'eng-US' => 'Lock' ),
                'descriptions' => array( 'eng-US' => '' )
            ),
            $group
        );
    }

    /**
     * Test service method for loading object state group throwing NotFoundException
     * @covers \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadObjectStateGroupThrowsNotFoundException()
    {
        $this->repository->getObjectStateService()->loadObjectStateGroup( PHP_INT_MAX );
    }

    /**
     * Test service method for loading object state groups
     * @covers \eZ\Publish\API\Repository\ObjectStateService::loadObjectStateGroups
     */
    public function testLoadObjectStateGroups()
    {
        $groups = $this->repository->getObjectStateService()->loadObjectStateGroups();

        $this->assertInternalType( 'array', $groups );
        $this->assertCount( 1, $groups );

        foreach ( $groups as $group )
        {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
                $group
            );
        }
    }

    /**
     * Test service method for loading object states within a group
     * @covers \eZ\Publish\API\Repository\ObjectStateService::loadObjectStates
     */
    public function testLoadObjectStates()
    {
        $objectStateService = $this->repository->getObjectStateService();
        $group = $objectStateService->loadObjectStateGroup( 2 );
        $states = $this->repository->getObjectStateService()->loadObjectStates( $group );

        $this->assertInternalType( 'array', $states );
        $this->assertCount( 2, $states );

        $priority = 0;
        foreach ( $states as $state )
        {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
                $state
            );

            $this->assertEquals( $group->id, $state->getObjectStateGroup()->id );
            $this->assertEquals( $priority, $state->priority );
            $priority++;
        }
    }

    /**
     * Test service method for updating object state group
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup
     */
    public function testUpdateObjectStateGroup()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        $groupUpdateStruct->identifier = 'test';
        $groupUpdateStruct->defaultLanguageCode = 'eng-GB';
        $groupUpdateStruct->names = array( 'eng-GB' => 'Test' );
        $groupUpdateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $group = $objectStateService->loadObjectStateGroup( 2 );

        $updatedGroup = $objectStateService->updateObjectStateGroup( $group, $groupUpdateStruct );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $updatedGroup
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 2,
                'identifier' => 'test',
                'defaultLanguageCode' => 'eng-GB',
                'languageCodes' => array( 'eng-GB' ),
                'names' => array( 'eng-GB' => 'Test' ),
                'descriptions' => array( 'eng-GB' => 'Test description' )
            ),
            $updatedGroup
        );
    }

    /**
     * Test service method for partially updating object state group
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup
     */
    public function testPartiallyUpdateObjectStateGroup()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        $groupUpdateStruct->defaultLanguageCode = 'eng-GB';
        $groupUpdateStruct->names = array( 'eng-GB' => 'Test' );

        $group = $objectStateService->loadObjectStateGroup( 2 );

        $updatedGroup = $objectStateService->updateObjectStateGroup( $group, $groupUpdateStruct );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $updatedGroup
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 2,
                'identifier' => 'ez_lock',
                'defaultLanguageCode' => 'eng-GB',
                'languageCodes' => array( 'eng-GB' ),
                'names' => array( 'eng-GB' => 'Test' ),
                // descriptions array should have an empty value for eng-GB
                // without the original descriptions
                // since the descriptions were not in the update struct and we're changing default language
                'descriptions' => array( 'eng-GB' => '' )
            ),
            $updatedGroup
        );
    }

    /**
     * Test service method for updating object state group throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectStateGroup
     */
    public function testUpdateObjectStateGroupThrowsInvalidArgumentException()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct( 'test' );
        $groupCreateStruct->defaultLanguageCode = 'eng-GB';
        $groupCreateStruct->names = array( 'eng-GB' => 'Test' );
        $groupCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $createdGroup = $objectStateService->createObjectStateGroup( $groupCreateStruct );

        $groupUpdateStruct = $objectStateService->newObjectStateGroupUpdateStruct();
        $groupUpdateStruct->identifier = 'ez_lock';

        $objectStateService->updateObjectStateGroup( $createdGroup, $groupUpdateStruct );
    }

    /**
     * Test service method for deleting object state group
     * @covers \eZ\Publish\API\Repository\ObjectStateService::deleteObjectStateGroup
     */
    public function testDeleteObjectStateGroup()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $group = $objectStateService->loadObjectStateGroup( 2 );

        $objectStateService->deleteObjectStateGroup( $group );

        try
        {
            $objectStateService->loadObjectStateGroup( 2 );
            $this->fail( "Successfully loaded object state group after deleting it" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }

        try
        {
            $objectStateService->loadObjectState( 1 );
            $this->fail( "Successfully loaded object state from deleted group" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }

        try
        {
            $objectStateService->loadObjectState( 2 );
            $this->fail( "Successfully loaded object state from deleted group" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test service method for creating object state
     * @covers \eZ\Publish\API\Repository\ObjectStateService::createObjectState
     */
    public function testCreateObjectState()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $group = $objectStateService->loadObjectStateGroup( 2 );

        $stateCreateStruct = $objectStateService->newObjectStateCreateStruct( 'test' );
        $stateCreateStruct->priority = 2;
        $stateCreateStruct->defaultLanguageCode = 'eng-GB';
        $stateCreateStruct->names = array( 'eng-GB' => 'Test' );
        $stateCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $createdState = $objectStateService->createObjectState(
            $group,
            $stateCreateStruct
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $createdState
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 3,
                'identifier' => 'test',
                'priority' => 2,
                'defaultLanguageCode' => 'eng-GB',
                'languageCodes' => array( 'eng-GB' ),
                'names' => array( 'eng-GB' => 'Test' ),
                'descriptions' => array( 'eng-GB' => 'Test description' )
            ),
            $createdState
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $createdState->getObjectStateGroup()
        );

        $this->assertEquals( $group->id, $createdState->getObjectStateGroup()->id );
    }

    /**
     * Test service method for creating object state in empty group
     * @covers \eZ\Publish\API\Repository\ObjectStateService::createObjectState
     */
    public function testCreateObjectStateInEmptyGroup()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct( 'test' );
        $groupCreateStruct->defaultLanguageCode = 'eng-GB';
        $groupCreateStruct->names = array( 'eng-GB' => 'Test' );
        $groupCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $createdGroup = $objectStateService->createObjectStateGroup( $groupCreateStruct );

        $stateCreateStruct = $objectStateService->newObjectStateCreateStruct( 'test' );
        $stateCreateStruct->priority = 2;
        $stateCreateStruct->defaultLanguageCode = 'eng-GB';
        $stateCreateStruct->names = array( 'eng-GB' => 'Test' );
        $stateCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $createdState = $objectStateService->createObjectState(
            $createdGroup,
            $stateCreateStruct
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $createdState
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 3,
                'identifier' => 'test',
                'priority' => 0,
                'defaultLanguageCode' => 'eng-GB',
                'languageCodes' => array( 'eng-GB' ),
                'names' => array( 'eng-GB' => 'Test' ),
                'descriptions' => array( 'eng-GB' => 'Test description' )
            ),
            $createdState
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $createdState->getObjectStateGroup()
        );

        $this->assertEquals( $createdGroup->id, $createdState->getObjectStateGroup()->id );
        $this->assertGreaterThan( 0, $objectStateService->getContentCount( $createdState ) );
    }

    /**
     * Test service method for creating object state throwing InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\ObjectStateService::createObjectState
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateObjectStateThrowsInvalidArgumentException()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $group = $objectStateService->loadObjectStateGroup( 2 );

        $stateCreateStruct = $objectStateService->newObjectStateCreateStruct( 'not_locked' );
        $stateCreateStruct->priority = 2;
        $stateCreateStruct->defaultLanguageCode = 'eng-GB';
        $stateCreateStruct->names = array( 'eng-GB' => 'Test' );
        $stateCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $objectStateService->createObjectState(
            $group,
            $stateCreateStruct
        );
    }

    /**
     * Test service method for loading object state
     * @covers \eZ\Publish\API\Repository\ObjectStateService::loadObjectState
     */
    public function testLoadObjectState()
    {
        $state = $this->repository->getObjectStateService()->loadObjectState( 1 );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $state
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'identifier' => 'not_locked',
                'priority' => 0,
                'defaultLanguageCode' => 'eng-US',
                'languageCodes' => array( 'eng-US' ),
                'names' => array( 'eng-US' => 'Not locked' ),
                'descriptions' => array( 'eng-US' => '' )
            ),
            $state
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $state->getObjectStateGroup()
        );

        $this->assertEquals( 2, $state->getObjectStateGroup()->id );
    }

    /**
     * Test service method for loading object state throwing NotFoundException
     * @covers \eZ\Publish\API\Repository\ObjectStateService::loadObjectState
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadObjectStateThrowsNotFoundException()
    {
        $this->repository->getObjectStateService()->loadObjectState( PHP_INT_MAX );
    }

    /**
     * Test service method for updating object state
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectState
     */
    public function testUpdateObjectState()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $stateUpdateStruct = $objectStateService->newObjectStateUpdateStruct();
        $stateUpdateStruct->identifier = 'test';
        $stateUpdateStruct->defaultLanguageCode = 'eng-GB';
        $stateUpdateStruct->names = array( 'eng-GB' => 'Test' );
        $stateUpdateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $state = $objectStateService->loadObjectState( 1 );

        $updatedState = $objectStateService->updateObjectState( $state, $stateUpdateStruct );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $updatedState
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'identifier' => 'test',
                'priority' => 0,
                'defaultLanguageCode' => 'eng-GB',
                'languageCodes' => array( 'eng-GB' ),
                'names' => array( 'eng-GB' => 'Test' ),
                'descriptions' => array( 'eng-GB' => 'Test description' )
            ),
            $updatedState
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $updatedState->getObjectStateGroup()
        );

        $this->assertEquals( $state->getObjectStateGroup()->id, $updatedState->getObjectStateGroup()->id );
    }

    /**
     * Test service method for partially updating object state
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectState
     */
    public function testPartiallyUpdateObjectState()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $stateUpdateStruct = $objectStateService->newObjectStateUpdateStruct();
        $stateUpdateStruct->identifier = 'test';
        $stateUpdateStruct->names = array( 'eng-US' => 'Test' );

        $state = $objectStateService->loadObjectState( 1 );

        $updatedState = $objectStateService->updateObjectState( $state, $stateUpdateStruct );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $updatedState
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'identifier' => 'test',
                'priority' => 0,
                'defaultLanguageCode' => 'eng-US',
                'languageCodes' => array( 'eng-US' ),
                'names' => array( 'eng-US' => 'Test' ),
                // Original value of empty description for eng-US should be kept
                'descriptions' => array( 'eng-US' => '' )
            ),
            $updatedState
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $updatedState->getObjectStateGroup()
        );

        $this->assertEquals( $state->getObjectStateGroup()->id, $updatedState->getObjectStateGroup()->id );
    }

    /**
     * Test service method for updating object state throwing InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\ObjectStateService::updateObjectState
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateObjectStateThrowsInvalidArgumentException()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $stateUpdateStruct = $objectStateService->newObjectStateUpdateStruct();
        $stateUpdateStruct->identifier = 'locked';

        $state = $objectStateService->loadObjectState( 1 );

        $objectStateService->updateObjectState( $state, $stateUpdateStruct );
    }

    /**
     * Test service method for setting the priority of object state
     * @covers \eZ\Publish\API\Repository\ObjectStateService::setPriorityOfObjectState
     */
    public function testSetPriorityOfObjectState()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $state = $objectStateService->loadObjectState( 2 );
        $objectStateService->setPriorityOfObjectState( $state, 0 );

        $firstState = $objectStateService->loadObjectState( 1 );
        $this->assertEquals( 1, $firstState->priority );

        $secondState = $objectStateService->loadObjectState( 2 );
        $this->assertEquals( 0, $secondState->priority );
    }

    /**
     * Test service method for deleting object state
     * @covers \eZ\Publish\API\Repository\ObjectStateService::deleteObjectState
     */
    public function testDeleteObjectState()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $state = $objectStateService->loadObjectState( 1 );
        $objectStateService->deleteObjectState( $state );

        try
        {
            $objectStateService->loadObjectState( 1 );
            $this->fail( "Successfully loaded object state after deleting it" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }

        $this->assertEquals( 0, $objectStateService->getContentCount( $state ) );
        $this->assertGreaterThan(
            0,
            $objectStateService->getContentCount(
                $objectStateService->loadObjectState( 2 )
            )
        );
    }

    /**
     * Test service method for setting the object state to content
     * @covers \eZ\Publish\API\Repository\ObjectStateService::setContentState
     */
    public function testSetContentState()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $state = $objectStateService->loadObjectState( 2 );
        $group = $state->getObjectStateGroup();
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 4 );
        $objectStateService->setContentState(
            $contentInfo,
            $group,
            $state
        );

        $newObjectState = $objectStateService->getContentState( $contentInfo, $group );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $newObjectState
        );

        $this->assertEquals( 2, $newObjectState->id );
    }

    /**
     * Test service method for setting the object state to content throwing InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\ObjectStateService::setContentState
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testSetContentStateThrowsInvalidArgumentException()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $groupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct( 'test' );
        $groupCreateStruct->defaultLanguageCode = 'eng-GB';
        $groupCreateStruct->names = array( 'eng-GB' => 'Test' );
        $groupCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $createdGroup = $objectStateService->createObjectStateGroup( $groupCreateStruct );

        $stateCreateStruct = $objectStateService->newObjectStateCreateStruct( 'test' );
        $stateCreateStruct->priority = 2;
        $stateCreateStruct->defaultLanguageCode = 'eng-GB';
        $stateCreateStruct->names = array( 'eng-GB' => 'Test' );
        $stateCreateStruct->descriptions = array( 'eng-GB' => 'Test description' );

        $createdState = $objectStateService->createObjectState(
            $createdGroup,
            $stateCreateStruct
        );

        $objectStateService->setContentState(
            $this->repository->getContentService()->loadContentInfo( 4 ),
            $objectStateService->loadObjectStateGroup( 2 ),
            $createdState
        );
    }

    /**
     * Test service method for getting the object state of content
     * @covers \eZ\Publish\API\Repository\ObjectStateService::getContentState
     */
    public function testGetContentState()
    {
        $objectStateService = $this->repository->getObjectStateService();

        $objectState = $objectStateService->getContentState(
            $this->repository->getContentService()->loadContentInfo( 4 ),
            $objectStateService->loadObjectStateGroup( 2 )
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectState',
            $objectState
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'identifier' => 'not_locked',
                'priority' => 0,
                'defaultLanguageCode' => 'eng-US',
                'languageCodes' => array( 'eng-US' ),
                'names' => array( 'eng-US' => 'Not locked' ),
                'descriptions' => array( 'eng-US' => '' )
            ),
            $objectState
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroup',
            $objectState->getObjectStateGroup()
        );

        $this->assertEquals( 2, $objectState->getObjectStateGroup()->id );
    }

    /**
     * Test service method for getting the count of content assigned to object state
     * @covers \eZ\Publish\API\Repository\ObjectStateService::getContentCount
     */
    public function testGetContentCount()
    {
        $objectStateService = $this->repository->getObjectStateService();
        $state = $objectStateService->loadObjectState( 1 );
        $count = $objectStateService->getContentCount( $state );

        $this->assertGreaterThan( 0, $count );
    }

    /**
     * Test service method for creating a new object state create struct object
     * @covers \eZ\Publish\API\Repository\ObjectStateService::newObjectStateCreateStruct
     */
    public function testNewObjectStateCreateStruct()
    {
        $objectStateCreateStruct = $this->repository->getObjectStateService()->newObjectStateCreateStruct( 'test' );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateCreateStruct',
            $objectStateCreateStruct
        );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => 'test',
                'priority' => false,
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null
            ),
            $objectStateCreateStruct
        );
    }

    /**
     * Test service method for creating a new object state update struct object
     * @covers \eZ\Publish\API\Repository\ObjectStateService::newObjectStateUpdateStruct
     */
    public function testNewObjectStateUpdateStruct()
    {
        $objectStateUpdateStruct = $this->repository->getObjectStateService()->newObjectStateUpdateStruct();

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateUpdateStruct',
            $objectStateUpdateStruct
        );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => null,
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null
            ),
            $objectStateUpdateStruct
        );
    }

    /**
     * Test service method for creating a new object state group create struct object
     * @covers \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupCreateStruct
     */
    public function testNewObjectStateGroupCreateStruct()
    {
        $objectStateGroupCreateStruct = $this->repository->getObjectStateService()->newObjectStateGroupCreateStruct( 'test' );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupCreateStruct',
            $objectStateGroupCreateStruct
        );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => 'test',
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null
            ),
            $objectStateGroupCreateStruct
        );
    }

    /**
     * Test service method for creating a new object state group update struct object
     * @covers \eZ\Publish\API\Repository\ObjectStateService::newObjectStateGroupUpdateStruct
     */
    public function testNewObjectStateGroupUpdateStruct()
    {
        $objectStateGroupUpdateStruct = $this->repository->getObjectStateService()->newObjectStateGroupUpdateStruct();

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ObjectState\\ObjectStateGroupUpdateStruct',
            $objectStateGroupUpdateStruct
        );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => null,
                'defaultLanguageCode' => null,
                'names' => null,
                'descriptions' => null
            ),
            $objectStateGroupUpdateStruct
        );
    }
}
