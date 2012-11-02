<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\ObjectStateHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct,
    eZ\Publish\API\Repository\Exceptions\NotFoundException;

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
        $this->assertEquals( 'eng-US', $createdGroup->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $createdGroup->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Test' ), $createdGroup->name );
        $this->assertEquals( array( 'eng-US' => 'Test description' ), $createdGroup->description );
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
        $this->assertEquals( 'eng-US', $group->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $group->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Lock' ), $group->name );
        $this->assertEquals( array( 'eng-US' => '' ), $group->description );
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
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadGroupByIdentifier
     */
    public function testLoadGroupByIdentifier()
    {
        $group = $this->handler->loadGroupByIdentifier( 'ez_lock' );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group', $group );

        $this->assertEquals( 2, $group->id );
        $this->assertEquals( 'ez_lock', $group->identifier );
        $this->assertEquals( 'eng-US', $group->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $group->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Lock' ), $group->name );
        $this->assertEquals( array( 'eng-US' => '' ), $group->description );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadGroupByIdentifier
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadGroupByIdentifierThrowsNotFoundException()
    {
        $this->handler->loadGroup( 'unknown' );
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
        $this->assertEquals( 'eng-US', $groups[0]->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $groups[0]->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Lock' ), $groups[0]->name );
        $this->assertEquals( array( 'eng-US' => '' ), $groups[0]->description );
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
        $this->assertEquals( 'eng-US', $states[0]->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $states[0]->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Not locked' ), $states[0]->name );
        $this->assertEquals( array( 'eng-US' => '' ), $states[0]->description );
        $this->assertEquals( 0, $states[0]->priority );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $states[1] );

        $this->assertEquals( 2, $states[1]->id );
        $this->assertEquals( 2, $states[1]->groupId );
        $this->assertEquals( 'locked', $states[1]->identifier );
        $this->assertEquals( 'eng-US', $states[1]->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $states[1]->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Locked' ), $states[1]->name );
        $this->assertEquals( array( 'eng-US' => '' ), $states[1]->description );
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
        $this->assertEquals( 'eng-US', $updatedGroup->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $updatedGroup->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Test' ), $updatedGroup->name );
        $this->assertEquals( array( 'eng-US' => 'Test description' ), $updatedGroup->description );
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
        $this->assertEquals( 'eng-US', $createdState->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $createdState->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Test' ), $createdState->name );
        $this->assertEquals( array( 'eng-US' => 'Test description' ), $createdState->description );
        $this->assertEquals( 2, $createdState->priority );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::create
     */
    public function testCreateInEmptyGroup()
    {
        $createdGroup = $this->handler->createGroup( $this->getInputStructFixture() );
        $createdState = $this->handler->create( $createdGroup->id, $this->getInputStructFixture() );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $createdState );

        $this->assertEquals( 3, $createdState->id );
        $this->assertEquals( $createdGroup->id, $createdState->groupId );
        $this->assertEquals( 'test', $createdState->identifier );
        $this->assertEquals( 'eng-US', $createdState->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $createdState->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Test' ), $createdState->name );
        $this->assertEquals( array( 'eng-US' => 'Test description' ), $createdState->description );
        $this->assertEquals( 0, $createdState->priority );

        $this->assertEquals( $this->handler->getContentCount( 1 ), $this->handler->getContentCount( $createdState->id ) );
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
        $this->assertEquals( 'eng-US', $state->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $state->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Not locked' ), $state->name );
        $this->assertEquals( array( 'eng-US' => '' ), $state->description );
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
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadByIdentifier
     */
    public function testLoadByIdentifier()
    {
        $state = $this->handler->loadByIdentifier( 'not_locked', 2 );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $state );

        $this->assertEquals( 1, $state->id );
        $this->assertEquals( 2, $state->groupId );
        $this->assertEquals( 'not_locked', $state->identifier );
        $this->assertEquals( 'eng-US', $state->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $state->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Not locked' ), $state->name );
        $this->assertEquals( array( 'eng-US' => '' ), $state->description );
        $this->assertEquals( 0, $state->priority );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::loadByIdentifier
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadByIdentifierThrowsNotFoundException()
    {
        $this->handler->loadByIdentifier( 'unknown', 2 );
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
        $this->assertEquals( 'eng-US', $updatedState->defaultLanguage );
        $this->assertEquals( array( 'eng-US' ), $updatedState->languageCodes );
        $this->assertEquals( array( 'eng-US' => 'Test' ), $updatedState->name );
        $this->assertEquals( array( 'eng-US' => 'Test description' ), $updatedState->description );
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
        $expectedCount = $this->handler->getContentCount( 1 );
        $this->handler->delete( 1 );
        try
        {
            $this->handler->load( 1 );
            $this->fail( 'Successfully loaded deleted object state' );
        }
        catch( NotFoundException $e )
        {
            // Do nothing
        }

        $this->assertEquals( 0, $this->handler->getContentCount( 1 ) );
        $this->assertEquals( $expectedCount, $this->handler->getContentCount( 2 ) );
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
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::setContentState
     */
    public function testSetContentState()
    {
        $returnValue = $this->handler->setContentState( 14, 2, 2 );
        $this->assertEquals( true, $returnValue );

        $newObjectState = $this->handler->getContentState( 14, 2 );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $newObjectState );
        $this->assertEquals( 2, $newObjectState->id );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::getContentState
     */
    public function testGetContentState()
    {
        $objectState = $this->handler->getContentState( 14, 2 );

        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState', $objectState );
        $this->assertEquals( 1, $objectState->id );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::getContentCount
     */
    public function testGetContentCount()
    {
        $count = $this->handler->getContentCount( 1 );

        // 9 is the count of objects in test fixtures as of writing
        $this->assertGreaterThanOrEqual( 9, $count );
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
        $inputStruct->defaultLanguage = 'eng-US';
        $inputStruct->name = array( 'eng-US' => 'Test' );
        $inputStruct->description = array( 'eng-US' => 'Test description' );

        return $inputStruct;
    }
}
