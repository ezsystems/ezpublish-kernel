<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,

    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,

    ezp\Persistence\Storage\Legacy\Exception,

    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\Type\Handler,
    ezp\Persistence\Storage\Legacy\Content\Type\Mapper,
    ezp\Persistence\Storage\Legacy\Content\Type\Gateway;

/**
 * Test case for Content Type Handler.
 */
class ContentTypeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::__construct
     */
    public function testCtor()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapper = $this->getMapperMock();
        $handler = new Handler( $gatewayMock, $mapper );

        $this->assertAttributeSame(
            $gatewayMock,
            'contentTypeGateway',
            $handler
        );
        $this->assertAttributeSame(
            $mapper,
            'mapper',
            $handler
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::createGroup
     */
    public function testCreateGroup()
    {
        $createStruct = new GroupCreateStruct();

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'createGroupFromCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\Group\\CreateStruct'
                )
            )
            ->will(
                $this->returnValue( new Group() )
            );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'insertGroup' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\Group'
                )
            )
            ->will( $this->returnValue( 23 ) );

        $handler = new Handler( $gatewayMock, $mapperMock );
        $group = $handler->createGroup(
            new GroupCreateStruct()
        );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type\\Group',
            $group
        );
        $this->assertEquals(
            23,
            $group->id
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::updateGroup
     */
    public function testUpdateGroup()
    {
        $updateStruct = new GroupUpdateStruct();
        $updateStruct->id = 23;

        $mapperMock = $this->getMapperMock();

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'updateGroup' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\Group\\UpdateStruct'
                )
            );

        $handlerMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Handler',
            array( 'loadGroup' ),
            array( $gatewayMock, $mapperMock )
        );
        $handlerMock->expects( $this->once() )
            ->method( 'loadGroup' )
            ->with(
                $this->equalTo( 23 )
            )->will(
                $this->returnValue( new Group() )
            );

        $res = $handlerMock->updateGroup(
            $updateStruct
        );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type\\Group',
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::deleteGroup
     */
    public function testDeleteGroupSuccess()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'countTypesInGroup' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( 0 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteGroup' )
            ->with( $this->equalTo( 23 ) );

        $handler = new Handler( $gatewayMock, $this->getMapperMock() );
        $handler->deleteGroup( 23 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::deleteGroup
     * @covers ezp\Persistence\Storage\Legacy\Exception\GroupNotEmpty
     * @expectedException ezp\Persistence\Storage\Legacy\Exception\GroupNotEmpty
     * @expectedExceptionMessage Group with ID "23" is not empty.
     */
    public function testDeleteGroupFailure()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'countTypesInGroup' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( 42 ) );
        $gatewayMock->expects( $this->never() )
            ->method( 'deleteGroup' );

        $handler = new Handler( $gatewayMock, $this->getMapperMock() );
        $handler->deleteGroup( 23 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::loadGroup
     */
    public function testLoadGroup()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadGroupData' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array() ) );

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'extractGroupsFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new Group() ) ) );

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->loadGroup( 23 );

        $this->assertEquals(
            new Group(),
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadAllGroupsData' )
            ->will( $this->returnValue( array() ) );

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'extractGroupsFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new Group() ) ) );

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->loadAllGroups();

        $this->assertEquals(
            array( new Group() ),
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::loadContentTypes
     */
    public function testLoadContentTypes()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadTypesDataForGroup' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) )
            ->will( $this->returnValue( array() ) );

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'extractTypesFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new Type() ) ) );

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->loadContentTypes( 23, 0 );

        $this->assertEquals(
            array( new Type() ),
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::load
     */
    public function testLoad()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadTypeData' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            )
            ->will( $this->returnValue( array() ) );

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'extractTypesFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will(
                $this->returnValue(
                    array( new Type() )
                )
            );

        $handler = new Handler( $gatewayMock, $mapperMock );
        $type = $handler->load( 23, 1 );

        $this->assertEquals(
            new Type(),
            $type,
            'Type not loaded correctly'
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::load
     */
    public function testLoadDefaultVersion()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadTypeData' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 0 )
            )
            ->will( $this->returnValue( array() ) );

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'extractTypesFromRows' )
            ->will(
                $this->returnValue(
                    array( new Type() )
                )
            );

        $handler = new Handler( $gatewayMock, $mapperMock );
        $type = $handler->load( 23 );

        $this->assertEquals(
            new Type(),
            $type,
            'Type not loaded correctly'
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::create
     */
    public function testCreate()
    {
        $createStructFix = $this->getContenTypeCreateStructFixture();
        $createStructClone = clone $createStructFix;

        $gatewayMock = $this->getMockForAbstractClass(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway'
        );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertType' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type'
                )
            )
            ->will( $this->returnValue( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'insertGroupAssignement' )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            );
        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertFieldDefinition' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 ),
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Type\\FieldDefinition' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition' )
            )
            ->will( $this->returnValue( 42 ) );

        $mapperMock = $this->getMapperMock(
            array(
                'toStorageFieldDefinition'
            )
        );
        $mapperMock->expects( $this->exactly( 2 ) )
            ->method( 'toStorageFieldDefinition' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Type\\FieldDefinition' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition' )
            );

        $handler = new Handler( $gatewayMock, $mapperMock );
        $type = $handler->create( $createStructFix );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type',
            $type,
            'Incorrect type returned from create()'
        );
        $this->assertEquals(
            23,
            $type->id,
            'Incorrect ID for Type.'
        );

        $this->assertEquals(
            42,
            $type->fieldDefinitions[0]->id,
            'Field definition ID not set correctly'
        );
        $this->assertEquals(
            42,
            $type->fieldDefinitions[1]->id,
            'Field definition ID not set correctly'
        );

        $this->assertEquals(
            $createStructClone,
            $createStructFix,
            'Create struct manipulated'
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::update
     */
    public function testUpdate()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'updateType' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\UpdateStruct'
                )
            );

        $handlerMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Handler',
            array( 'load' ),
            array( $gatewayMock, $this->getMapperMock() )
        );
        $handlerMock->expects( $this->once() )
            ->method( 'load' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            )
            ->will( $this->returnValue( new Type() ) );

        $res = $handlerMock->update(
            23, 1, new UpdateStruct()
        );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::delete
     */
    public function testDeleteSuccess()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'countInstancesOfType' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) )
            ->will( $this->returnValue( 0 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteGroupAssignementsForType' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteFieldDefinitionsForType' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteType' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) );

        $mapperMock = $this->getMapperMock();

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->delete( 23, 0 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::delete
     * @covers ezp\Persistence\Storage\Legacy\Exception\TypeStillHasContent
     * @expectedException ezp\Persistence\Storage\Legacy\Exception\TypeStillHasContent
     * @expectedExceptionMessage Type with ID "23" in status "0" still has content instances and can therefore not be deleted.
     */
    public function testDeleteFailure()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'countInstancesOfType' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 0 ) )
            // An instance of this type exists
            ->will( $this->returnValue( 1 ) );
        $gatewayMock->expects( $this->never() )
            ->method( 'deleteGroupAssignementsForType' );
        $gatewayMock->expects( $this->never() )
            ->method( 'deleteFieldDefinitionsForType' );
        $gatewayMock->expects( $this->never() )
            ->method( 'deleteType' );

        $mapperMock = $this->getMapperMock();

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->delete( 23, 0 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::createVersion
     */
    public function testCreateVersion()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'createCreateStructFromType' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type'
                )
            )->will(
                $this->returnValue( new CreateStruct() )
            );

        $handlerMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Handler',
            array( 'load', 'create' ),
            array( $gatewayMock, $mapperMock )
        );
        $handlerMock->expects( $this->once() )
            ->method( 'load' )
            ->with(
                $this->equalTo( 23, 0 )
            )->will(
                $this->returnValue(
                    new Type()
                )
            );
        $handlerMock->expects( $this->once() )
            ->method( 'create' )
            ->with(
                $this->logicalAnd(
                    $this->attributeEqualTo(
                        'status', 1
                    ),
                    $this->attributeEqualTo(
                        'modifierId', 42
                    ),
                    $this->attribute(
                        $this->greaterThanOrEqual(
                            time()
                        ),
                        'modified'
                    )
                )
            )->will(
                $this->returnValue( new Type() )
            );

        $res = $handlerMock->createVersion(
            42, 23, 0, 1
        );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::copy
     */
    public function testCopy()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'createCreateStructFromType' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type'
                )
            )->will(
                $this->returnValue( new CreateStruct() )
            );

        $handlerMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Handler',
            array( 'load', 'create' ),
            array( $gatewayMock, $mapperMock )
        );
        $handlerMock->expects( $this->once() )
            ->method( 'load' )
            ->with(
                $this->equalTo( 23, 0 )
            )->will(
                $this->returnValue(
                    new Type()
                )
            );
        $handlerMock->expects( $this->once() )
            ->method( 'create' )
            ->with(
                $this->logicalAnd(
                    $this->attributeEqualTo(
                        'modifierId', 42
                    ),
                    $this->attribute(
                        $this->greaterThanOrEqual(
                            time()
                        ),
                        'modified'
                    ),
                    $this->attributeEqualTo(
                        'creatorId', 42
                    ),
                    $this->attribute(
                        $this->greaterThanOrEqual(
                            time()
                        ),
                        'created'
                    )
                )
            )->will(
                $this->returnValue( new Type() )
            );

        $res = $handlerMock->copy(
            42, 23, 0
        );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::link
     */
    public function testLink()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'insertGroupAssignement' )
            ->with(
                $this->equalTo( 3 ),
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            );

        $mapperMock = $this->getMapperMock();

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->link( 3, 23, 1 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::unlink
     */
    public function testUnlinkSuccess()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'countGroupsForType' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            )->will( $this->returnValue( 2 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'deleteGroupAssignement' )
            ->with(
                $this->equalTo( 3 ),
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            );

        $mapperMock = $this->getMapperMock();

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->unlink( 3, 23, 1 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::unlink
     * @covers ezp\Persistence\Storage\Legacy\Exception\RemoveLastGroupFromType
     * @expectedException ezp\Persistence\Storage\Legacy\Exception\RemoveLastGroupFromType
     * @expectedExceptionMessage Type with ID "23" in status "1" cannot be unlinked from its last group.
     */
    public function testUnlinkFailure()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'countGroupsForType' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            )
            // Only 1 group assigned
            ->will( $this->returnValue( 1 ) );

        $mapperMock = $this->getMapperMock();

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->unlink( 3, 23, 1 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $mapperMock = $this->getMapperMock(
            array( 'toStorageFieldDefinition' )
        );
        $mapperMock->expects( $this->once() )
            ->method( 'toStorageFieldDefinition' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\FieldDefinition'
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition'
                )
            );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'insertFieldDefinition' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\FieldDefinition'
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition'
                )
            )->will(
                $this->returnValue( 42 )
            );

        $fieldDef = new FieldDefinition();

        $handler = new Handler( $gatewayMock, $mapperMock );
        $handler->addFieldDefinition( 23, 1, $fieldDef );

        $this->assertEquals(
            42,
            $fieldDef->id
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::removeFieldDefinition
     */
    public function testRemoveFieldDefinition()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteFieldDefinition' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 ),
                $this->equalTo( 42 )
            );

        $handler = new Handler( $gatewayMock, $this->getMapperMock() );
        $res = $handler->removeFieldDefinition( 23, 1, 42 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Handler::updateFieldDefinition
     */
    public function testUpdateFieldDefinition()
    {
        $mapperMock = $this->getMapperMock(
            array( 'toStorageFieldDefinition' )
        );
        $mapperMock->expects( $this->once() )
            ->method( 'toStorageFieldDefinition' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\FieldDefinition'
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldDefinition'
                )
            );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'updateFieldDefinition' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type\\FieldDefinition'
                )
            );

        $fieldDef = new FieldDefinition();

        $handler = new Handler( $gatewayMock, $mapperMock );
        $res = $handler->updateFieldDefinition( 23, 1, $fieldDef );

        $this->assertNull( $res );
    }

    /**
     * Returns a gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected function getGatewayMock()
    {
        return $this->getMockForAbstractClass(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway'
        );
    }

    /**
     * Returns a mapper mock
     *
     * @return ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Mapper
     */
    protected function getMapperMock( $methods = array() )
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Mapper',
            $methods,
            array(),
            '',
            false
        );
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \ezp\Persistence\Content\Type\CreateStruct
     */
    protected function getContenTypeCreateStructFixture()
    {
        $struct = new CreateStruct();
        $struct->status = 1;
        $struct->groupIds = array(
            42,
        );

        $fieldDefName = new FieldDefinition();
        $fieldDefShortDescription = new FieldDefinition();

        $struct->fieldDefinitions = array(
            $fieldDefName,
            $fieldDefShortDescription
        );

        return $struct;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
