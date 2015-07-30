<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\ObjectState as SPIObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group as SPIObjectStateGroup;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct as SPIInputStruct;

/**
 * Test case for Persistence\Cache\ObjectStateHandler.
 */
class ObjectStateHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ObjectStateHandler::createGroup
     */
    public function testCreateGroup()
    {
        $inputStruct = new SPIInputStruct(
            array('identifier' => 'test_state_group', 'name' => 'Test State Group')
        );
        $expectedGroup = new SPIObjectStateGroup(
            array(
                'id' => 1,
                'identifier' => 'test_state_group',
                'name' => 'Test State Group',
            )
        );
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('createGroup')
            ->with($inputStruct)
            ->will(
                $this->returnValue($expectedGroup)
            );

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('objectstategroup', 'all')
            ->will($this->returnValue(null));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('objectstategroup', 1)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group'));

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $group = $handler->createGroup($inputStruct);
        $this->assertEquals($group, $expectedGroup);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ObjectStateHandler::loadGroup
     */
    public function testLoadGroup()
    {
        $expectedGroup = new SPIObjectStateGroup(
            array(
                'id' => 1,
                'identifier' => 'test_state_group',
                'name' => 'Test State Group',
            )
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('objectstategroup', 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadGroup')
            ->with(1)
            ->will($this->returnValue($expectedGroup));

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $group = $handler->loadGroup(1);
        $this->assertEquals($group, $expectedGroup);
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ObjectStateHandler::loadGroup
     * @depends testLoadGroup
     */
    public function testLoadGroupHasCache()
    {
        $expectedGroup = new SPIObjectStateGroup(
            array(
                'id' => 1,
                'identifier' => 'test_state_group',
                'name' => 'Test State Group',
            )
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('objectstategroup', 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedGroup));

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $group = $handler->loadGroup(1);
        $this->assertEquals($group, $expectedGroup);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadGroupByIdentifier
     */
    public function testLoadGroupByIdentifier()
    {
        $expectedGroup = new SPIObjectStateGroup(
            array(
                'id' => 1,
                'identifier' => 'test_state_group',
                'name' => 'Test State Group',
            )
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadGroupByIdentifier')
            ->with($expectedGroup->identifier)
            ->will($this->returnValue($expectedGroup));

        $group = $this->persistenceCacheHandler->objectStateHandler()
            ->loadGroupByIdentifier('test_state_group');
        $this->assertEquals($group, $expectedGroup);
    }

    public function generateObjectGroupsArray()
    {
        $result = array();
        for ($i = 1; $i <= 20; ++$i) {
            $result[] = new SPIObjectStateGroup(
                array(
                    'id' => $i,
                    'identifier' => "test_state_group_${i}",
                    'name' => "Test State Group #${i}",
                )
            );
        }

        return $result;
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadAllGroups
     */
    public function testLoadAllGroups($offset = 0, $limit = -1)
    {
        $testGroups = $this->generateObjectGroupsArray();
        $testGroupIds = array_map(
            function ($group) {
                return $group->id;
            },
            $testGroups
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('objectstategroup', 'all')
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadAllGroups')
            ->with(0, -1)
            ->will($this->returnValue($testGroups));

        foreach ($testGroups as $group) {
            $this->cacheMock
                ->expects($this->at($group->id))
                ->method('getItem')
                ->with('objectstategroup', $group->id)
                ->will(
                    $this->returnCallback(
                        function ($cachekey, $i) use ($group) {
                            $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
                            $cacheItemMock
                                ->expects($this->once())
                                ->method('set')
                                ->with($group);

                            return $cacheItemMock;
                        }
                    )
                );
        }

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($testGroupIds);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $groups = $handler->loadAllGroups($offset, $limit);

        $expectedGroups = array_slice($testGroups, $offset, $limit > -1 ?: null);
        $this->assertEquals($groups, $expectedGroups);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadAllGroups
     */
    public function testLoadAllGroupsCached($offset = 0, $limit = -1)
    {
        $testGroups = $this->generateObjectGroupsArray($offset, $limit);
        $testGroupIds = array_map(
            function ($group) {
                return $group->id;
            },
            $testGroups
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('objectstategroup', 'all')
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($testGroupIds));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $expectedGroups = array_slice($testGroups, $offset, $limit > -1 ?: null);

        // loadGroup()
        foreach ($expectedGroups as $i => $group) {
            $this->cacheMock
                ->expects($this->at($i + 1))
                ->method('getItem')
                ->with('objectstategroup', $group->id)
                ->will(
                    $this->returnCallback(
                        function ($cachekey, $i) use ($group) {
                            $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
                            $cacheItemMock
                                ->expects($this->once())
                                ->method('get')
                                ->will($this->returnValue($group));

                            return $cacheItemMock;
                        }
                    )
                );
        }

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $groups = $handler->loadAllGroups($offset, $limit);
        $this->assertEquals($groups, $expectedGroups);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadAllGroups
     */
    public function testLoadAllGroupsWithOffsetLimit()
    {
        $this->testLoadAllGroups(7, 5);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadAllGroups
     */
    public function testLoadAllGroupsCachedWithOffsetLimit()
    {
        $this->testLoadAllGroupsCached(7, 5);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadObjectStates
     */
    public function testLoadObjectStates()
    {
        $testStates = array(
            new SPIObjectState(
                array(
                    'id' => 1,
                    'identifier' => 'test_state_1_group1',
                    'groupId' => 1,
                )
            ),
            new SPIObjectState(
                array(
                    'id' => 2,
                    'identifier' => 'test_state_2_group1',
                    'groupId' => 1,
                )
            ),
            new SPIObjectState(
                array(
                    'id' => 3,
                    'identifier' => 'test_state_3_group1',
                    'groupId' => 1,
                )
            ),
        );
        $testStateIds = array_map(
            function ($state) {
                return $state->id;
            },
            $testStates
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('objectstate', 'byGroup', 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadObjectStates')
            ->with(1)
            ->will($this->returnValue($testStates));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($testStateIds);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $states = $handler->loadObjectStates(1);
        $this->assertEquals($states, $testStates);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadObjectStates
     */
    public function testLoadObjectStatesCached()
    {
        $testStates = array(
            new SPIObjectState(
                array(
                    'id' => 1,
                    'identifier' => 'test_state_1_group1',
                    'groupId' => 1,
                )
            ),
            new SPIObjectState(
                array(
                    'id' => 2,
                    'identifier' => 'test_state_2_group1',
                    'groupId' => 1,
                )
            ),
            new SPIObjectState(
                array(
                    'id' => 3,
                    'identifier' => 'test_state_3_group1',
                    'groupId' => 1,
                )
            ),
        );
        $testStateIds = array_map(
            function ($state) {
                return $state->id;
            },
            $testStates
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('objectstate', 'byGroup', 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($testStateIds));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        // load()
        foreach ($testStates as $i => $state) {
            $this->cacheMock
                ->expects($this->at($i + 1))
                ->method('getItem')
                ->with('objectstate', $state->id)
                ->will(
                    $this->returnCallback(
                        function ($cachekey, $i) use ($state) {
                            $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
                            $cacheItemMock
                                ->expects($this->once())
                                ->method('get')
                                ->will($this->returnValue($state));

                            return $cacheItemMock;
                        }
                    )
                );
        }

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $states = $handler->loadObjectStates(1);
        $this->assertEquals($states, $testStates);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::updateGroup
     */
    public function testUpdateGroup()
    {
        $inputStruct = new SPIInputStruct(
            array('identifier' => 'test_state_group', 'name' => 'Test State Group (New)')
        );
        $expectedGroup = new SPIObjectStateGroup(
            array(
                'id' => 1,
                'identifier' => 'test_state_group_new',
                'name' => 'Test State Group (New)',
            )
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('updateGroup')
            ->with(1, $inputStruct)
            ->will($this->returnValue($expectedGroup));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('objectstategroup', 1);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $newGroup = $handler->updateGroup(1, $inputStruct);
        $this->assertEquals($newGroup, $expectedGroup);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('deleteGroup')
            ->with(1);

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('objectstategroup', 'all');
        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('objectstategroup', 1);
        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('objectstate', 'byGroup', 1);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $handler->deleteGroup(1);
    }

    /**
     * @covers eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::create
     */
    public function testCreate()
    {
        $inputStruct = new SPIInputStruct(
            array('identifier' => 'test_state', 'name' => 'Test State')
        );
        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
            )
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('create')
            ->with(1, $inputStruct)
            ->will($this->returnValue($expectedState));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('objectstate', 'byGroup', 1);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->create(1, $inputStruct);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::load
     */
    public function testLoad()
    {
        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
            )
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('objectstate', 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->will($this->returnValue($expectedState));

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->load(1);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::load
     * @depends testLoad
     */
    public function testLoadCached()
    {
        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
            )
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('objectstate', 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedState));

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->load(1);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::loadByIdentifier
     */
    public function testLoadByIdentifier()
    {
        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
            )
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('loadByIdentifier')
            ->with($expectedState->identifier, $expectedState->groupId)
            ->will($this->returnValue($expectedState));

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->loadByIdentifier($expectedState->identifier, $expectedState->groupId);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::update
     */
    public function testUpdate()
    {
        $inputStruct = new SPIInputStruct(
            array('identifier' => 'test_state_new', 'name' => 'Test State (new)')
        );

        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
            )
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('update')
            ->with($expectedState->id, $inputStruct)
            ->will($this->returnValue($expectedState));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('objectstate', $expectedState->id);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->update($expectedState->id, $inputStruct);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::setPriority
     */
    public function testSetPriority()
    {
        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
                'priority' => 1,
            )
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('setPriority')
            ->with($expectedState->id, 1)
            ->will($this->returnValue($expectedState));

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('objectstate', $expectedState->id);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->setPriority($expectedState->id, 1);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::delete
     */
    public function testDelete()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('delete')
            ->with(1);

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('objectstate', 1);
        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('objectstate', 'byGroup');

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->delete(1);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::setContentState
     */
    public function testSetContentState()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('setContentState')
            ->with(10, 1, 2);

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('objectstate', 'byContent', 10, 1);

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->setContentState(10, 1, 2);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::getContentState
     */
    public function testGetContentState()
    {
        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
                'priority' => 1,
            )
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('objectstate', 'byContent', 10, 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('getContentState')
            ->with(10, 1)
            ->will($this->returnValue($expectedState));

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->getContentState(10, 1, 2);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::getContentState
     */
    public function testGetContentStateCached()
    {
        $expectedState = new SPIObjectState(
            array(
                'id' => 1,
                'identifier' => 'test_state',
                'name' => 'Test State',
                'groupId' => 1,
            )
        );

        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('objectstate', 'byContent', 10, 1)
            ->will($this->returnValue($cacheItemMock));
        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(1));
        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        // load()
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('objectstate', $expectedState->id)
            ->will(
                $this->returnCallback(
                    function ($cachekey, $i) use ($expectedState) {
                        $cacheItemMock = $this->getMock('Stash\Interfaces\ItemInterface');
                        $cacheItemMock
                            ->expects($this->once())
                            ->method('get')
                            ->will($this->returnValue($expectedState));

                        return $cacheItemMock;
                    }
                )
            );

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $state = $handler->getContentState(10, 1);
        $this->assertEquals($state, $expectedState);
    }

    /**
     * @covers \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler::getContentCount
     */
    public function testGetContentCount()
    {
        $expectedCount = 2;

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler');
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('objectStateHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('getContentCount')
            ->with(1)
            ->will($this->returnValue($expectedCount));

        //$this->logger->logCall( __METHOD__, array( 'stateId' => $stateId ) );

        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $count = $handler->getContentCount(1);
        $this->assertEquals($count, $expectedCount);
    }
}
