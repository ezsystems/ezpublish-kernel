<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\ObjectStateHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;
use eZ\Publish\Core\Persistence\InMemory\Tests\HandlerTest,
    eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct,
    \eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for ObjectStateHandler using in memory storage.
 */
class ObjectStateHandlerTest extends HandlerTest
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $handler;

    /**
     * Setup the SectionHandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->handler = $this->persistenceHandler->objectStateHandler();
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::createGroup
     */
    public function testCreateGroup()
    {
        $createdGroup = $this->handler->createGroup( $this->getInputStructFixture() );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group', $createdGroup );

        $this->assertEquals( 3, $createdGroup->id );
        $this->assertEquals( 'test', $createdGroup->identifier );
        $this->assertEquals( 'eng-GB', $createdGroup->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $createdGroup->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Test' ), $createdGroup->name );
        $this->assertEquals( array( 'eng-GB' => 'Test description' ), $createdGroup->description );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadGroup
     */
    public function testLoadGroup()
    {
        $group = $this->handler->loadGroup( 2 );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group', $group );

        $this->assertEquals( 2, $group->id );
        $this->assertEquals( 'ez_lock', $group->identifier );
        $this->assertEquals( 'eng-GB', $group->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $group->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Lock' ), $group->name );
        $this->assertEquals( array( 'eng-GB' => '' ), $group->description );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadGroupThrowsNotFoundException()
    {
        $this->handler->loadGroup( PHP_INT_MAX );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $groups = $this->handler->loadAllGroups();

        $this->assertCount( 1, $groups );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group', $groups[0] );

        $this->assertEquals( 2, $groups[0]->id );
        $this->assertEquals( 'ez_lock', $groups[0]->identifier );
        $this->assertEquals( 'eng-GB', $groups[0]->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $groups[0]->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Lock' ), $groups[0]->name );
        $this->assertEquals( array( 'eng-GB' => '' ), $groups[0]->description );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadObjectStates
     */
    public function testLoadObjectStates()
    {
        $states = $this->handler->loadObjectStates( 2 );

        $this->assertCount( 2, $states );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $states[0] );

        $this->assertEquals( 1, $states[0]->id );
        $this->assertEquals( 2, $states[0]->groupId );
        $this->assertEquals( 'not_locked', $states[0]->identifier );
        $this->assertEquals( 'eng-GB', $states[0]->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $states[0]->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Not locked' ), $states[0]->name );
        $this->assertEquals( array( 'eng-GB' => '' ), $states[0]->description );
        $this->assertEquals( 0, $states[0]->priority );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $states[1] );

        $this->assertEquals( 2, $states[1]->id );
        $this->assertEquals( 2, $states[1]->groupId );
        $this->assertEquals( 'locked', $states[1]->identifier );
        $this->assertEquals( 'eng-GB', $states[1]->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $states[1]->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Locked' ), $states[1]->name );
        $this->assertEquals( array( 'eng-GB' => '' ), $states[1]->description );
        $this->assertEquals( 1, $states[1]->priority );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::updateGroup
     */
    public function testUpdateGroup()
    {
        $updatedGroup = $this->handler->updateGroup( 2, $this->getInputStructFixture() );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group', $updatedGroup );

        $this->assertEquals( 2, $updatedGroup->id );
        $this->assertEquals( 'test', $updatedGroup->identifier );
        $this->assertEquals( 'eng-GB', $updatedGroup->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $updatedGroup->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Test' ), $updatedGroup->name );
        $this->assertEquals( array( 'eng-GB' => 'Test description' ), $updatedGroup->description );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->handler->deleteGroup( 2 );

        try
        {
            $this->handler->loadGroup( 2 );
            $this->fail( 'Successfully loaded deleted object state group' );
        }
        catch( NotFoundException $e )
        {
            // Do nothing
        }

        try
        {
            $this->handler->load( 1 );
            $this->fail( 'Successfully loaded one of the states from deleted object state group' );
        }
        catch( NotFoundException $e )
        {
            // Do nothing
        }

        try
        {
            $this->handler->load( 2 );
            $this->fail( 'Successfully loaded one of the states from deleted object state group' );
        }
        catch( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::create
     */
    public function testCreate()
    {
        $createdState = $this->handler->create( 2, $this->getInputStructFixture() );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $createdState );

        $this->assertEquals( 3, $createdState->id );
        $this->assertEquals( 2, $createdState->groupId );
        $this->assertEquals( 'test', $createdState->identifier );
        $this->assertEquals( 'eng-GB', $createdState->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $createdState->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Test' ), $createdState->name );
        $this->assertEquals( array( 'eng-GB' => 'Test description' ), $createdState->description );
        $this->assertEquals( 2, $createdState->priority );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::load
     */
    public function testLoad()
    {
        $state = $this->handler->load( 1 );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $state );

        $this->assertEquals( 1, $state->id );
        $this->assertEquals( 2, $state->groupId );
        $this->assertEquals( 'not_locked', $state->identifier );
        $this->assertEquals( 'eng-GB', $state->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $state->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Not locked' ), $state->name );
        $this->assertEquals( array( 'eng-GB' => '' ), $state->description );
        $this->assertEquals( 0, $state->priority );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::load
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadThrowsNotFoundException()
    {
        $this->handler->load( PHP_INT_MAX );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::update
     */
    public function testUpdate()
    {
        $updatedState = $this->handler->update( 1, $this->getInputStructFixture() );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $updatedState );

        $this->assertEquals( 1, $updatedState->id );
        $this->assertEquals( 2, $updatedState->groupId );
        $this->assertEquals( 'test', $updatedState->identifier );
        $this->assertEquals( 'eng-GB', $updatedState->defaultLanguage );
        $this->assertEquals( array( 'eng-GB' ), $updatedState->languageCodes );
        $this->assertEquals( array( 'eng-GB' => 'Test' ), $updatedState->name );
        $this->assertEquals( array( 'eng-GB' => 'Test description' ), $updatedState->description );
        $this->assertEquals( 0, $updatedState->priority );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::setPriority
     */
    public function testSetPriority()
    {
        $this->handler->setPriority( 2, 0 );

        $firstObjectState = $this->handler->load( 1 );
        $this->assertEquals( 1, $firstObjectState->priority );

        $firstObjectState = $this->handler->load( 2 );
        $this->assertEquals( 0, $firstObjectState->priority );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::delete
     */
    public function testDelete()
    {
        $this->handler->delete( 2 );

        try
        {
            $this->handler->load( 2 );
            $this->fail( 'Successfully loaded deleted object state' );
        }
        catch( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::delete
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteThrowsNotFoundException()
    {
        $this->handler->delete( PHP_INT_MAX );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::setObjectState
     */
    public function testSetObjectState()
    {
        $returnValue = $this->handler->setObjectState( 14, 2, 2 );
        $this->assertEquals( true, $returnValue );

        $newObjectState = $this->handler->getObjectState( 14, 2 );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $newObjectState );
        $this->assertEquals( 2, $newObjectState->id );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::getObjectState
     */
    public function testGetObjectState()
    {
        $objectState = $this->handler->getObjectState( 14, 2 );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $objectState );
        $this->assertEquals( 1, $objectState->id );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::getContentCount
     */
    public function testGetContentCount()
    {
        $count = $this->handler->getContentCount( 1 );

        // 7 is the count of objects in test fixtures
        $this->assertEquals( 7, $count );
    }

    /**
     * Returns the input struct fixture for object states and groups
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct
     */
    protected function getInputStructFixture()
    {
        $inputStruct = new InputStruct();

        $inputStruct->identifier = 'test';
        $inputStruct->defaultLanguage = 'eng-GB';
        $inputStruct->name = array( 'eng-GB' => 'Test' );
        $inputStruct->description = array( 'eng-GB' => 'Test description' );

        return $inputStruct;
    }
}
