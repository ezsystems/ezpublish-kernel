<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Exception;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler;

/**
 * Test case for Content Type Handler.
 */
class ContentTypeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected $gatewayMock;

    /**
     * Mapper mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper
     */
    protected $mapperMock;

    /**
     * Update\Handler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler
     */
    protected $updateHandlerMock;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::__construct
     *
     * @return void
     */
    public function testCtor()
    {
        $handler = $this->getHandler();

        $this->assertAttributeSame(
            $this->getGatewayMock(),
            'contentTypeGateway',
            $handler
        );
        $this->assertAttributeSame(
            $this->getMapperMock(),
            'mapper',
            $handler
        );
        $this->assertAttributeSame(
            $this->getUpdateHandlerMock(),
            'updateHandler',
            $handler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::createGroup
     *
     * @return void
     */
    public function testCreateGroup()
    {
        $createStruct = new GroupCreateStruct();

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'createGroupFromCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group\\CreateStruct'
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
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group'
                )
            )
            ->will( $this->returnValue( 23 ) );

        $handler = $this->getHandler();
        $group = $handler->createGroup(
            new GroupCreateStruct()
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group',
            $group
        );
        $this->assertEquals(
            23,
            $group->id
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::updateGroup
     *
     * @return void
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
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group\\UpdateStruct'
                )
            );

        $handlerMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Handler',
            array( 'loadGroup' ),
            array( $gatewayMock, $mapperMock, $this->getUpdateHandlerMock() )
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
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group',
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::deleteGroup
     *
     * @return void
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

        $handler = $this->getHandler();
        $handler->deleteGroup( 23 );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::deleteGroup
     * @covers eZ\Publish\Core\Persistence\Legacy\Exception\GroupNotEmpty
     * @expectedException eZ\Publish\Core\Persistence\Legacy\Exception\GroupNotEmpty
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

        $handler = $this->getHandler();
        $handler->deleteGroup( 23 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadGroup
     *
     * @return void
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

        $handler = $this->getHandler();
        $res = $handler->loadGroup( 23 );

        $this->assertEquals(
            new Group(),
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadGroupByIdentifier
     *
     * @return void
     */
    public function testLoadGroupByIdentifier()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadGroupDataByIdentifier' )
            ->with( $this->equalTo( 'content' ) )
            ->will( $this->returnValue( array() ) );

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'extractGroupsFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new Group() ) ) );

        $handler = $this->getHandler();
        $res = $handler->loadGroupByIdentifier( 'content' );

        $this->assertEquals(
            new Group(),
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadAllGroups
     *
     * @return void
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

        $handler = $this->getHandler();
        $res = $handler->loadAllGroups();

        $this->assertEquals(
            array( new Group() ),
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadContentTypes
     *
     * @return void
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

        $handler = $this->getHandler();
        $res = $handler->loadContentTypes( 23, 0 );

        $this->assertEquals(
            array( new Type() ),
            $res
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadFromRows
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

        $handler = $this->getHandler();
        $type = $handler->load( 23, 1 );

        $this->assertEquals(
            new Type(),
            $type,
            'Type not loaded correctly'
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadFromRows
     * @expectedException \eZ\Publish\Core\Persistence\Legacy\Exception\TypeNotFound
     */
    public function testLoadNotFound()
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
                    array()
                )
            );

        $handler = $this->getHandler();
        $type = $handler->load( 23, 1 );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadFromRows
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

        $handler = $this->getHandler();
        $type = $handler->load( 23 );

        $this->assertEquals(
            new Type(),
            $type,
            'Type not loaded correctly'
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadByIdentifier
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadFromRows
     */
    public function testLoadByIdentifier()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadTypeDataByIdentifier' )
            ->with(
                $this->equalTo( 'blogentry' ),
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

        $handler = $this->getHandler();
        $type = $handler->loadByIdentifier( 'blogentry' );

        $this->assertEquals(
            new Type(),
            $type,
            'Type not loaded correctly'
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadByRemoteId
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::loadFromRows
     */
    public function testLoadByRemoteId()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadTypeDataByRemoteId' )
            ->with(
                $this->equalTo( 'someLongHash' ),
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

        $handler = $this->getHandler();
        $type = $handler->loadByRemoteId( 'someLongHash' );

        $this->assertEquals(
            new Type(),
            $type,
            'Type not loaded correctly'
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::create
     *
     * @return void
     */
    public function testCreate()
    {
        $createStructFix = $this->getContenTypeCreateStructFixture();
        $createStructClone = clone $createStructFix;

        $mapperMock = $this->getMapperMock(
            array(
                'toStorageFieldDefinition'
            )
        );

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'insertType' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type'
                )
            )
            ->will( $this->returnValue( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'insertGroupAssignment' )
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
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldDefinition' )
            )
            ->will( $this->returnValue( 42 ) );

        $mapperMock->expects( $this->exactly( 2 ) )
            ->method( 'toStorageFieldDefinition' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition' ),
                $this->isInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldDefinition' )
            );

        $handler = $this->getHandler();
        $type = $handler->create( $createStructFix );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::update
     *
     * @return void
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
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\UpdateStruct'
                )
            );

        $handlerMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Handler',
            array( 'load' ),
            array( $gatewayMock, $this->getMapperMock(), $this->getUpdateHandlerMock() )
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
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::delete
     *
     * @return void
     */
    public function testDeleteSuccess()
    {
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects(
            $this->once()
        )->method(
            "loadTypeData"
        )->with(
            $this->equalTo( 23 ),
            $this->equalTo( 0 )
        )->will(
            $this->returnValue( array( 42 ) )
        );

        $gatewayMock->expects(
            $this->once()
        )->method(
            "countInstancesOfType"
        )->with(
            $this->equalTo( 23 )
        )->will(
            $this->returnValue( 0 )
        );

        $gatewayMock->expects(
            $this->once()
        )->method(
            "delete"
        )->with(
            $this->equalTo( 23 ),
            $this->equalTo( 0 )
        );

        $handler = $this->getHandler();
        $res = $handler->delete( 23, 0 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::delete
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testDeleteThrowsNotFoundException()
    {
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects(
            $this->once()
        )->method(
            "loadTypeData"
        )->with(
            $this->equalTo( 23 ),
            $this->equalTo( 0 )
        )->will(
            $this->returnValue( array() )
        );

        $gatewayMock->expects( $this->never() )->method( "delete" );

        $handler = $this->getHandler();
        $res = $handler->delete( 23, 0 );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::delete
     * @expectedException \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function testDeleteThrowsBadStateException()
    {
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects(
            $this->once()
        )->method(
            "loadTypeData"
        )->with(
            $this->equalTo( 23 ),
            $this->equalTo( 0 )
        )->will(
            $this->returnValue( array( 42 ) )
        );

        $gatewayMock->expects(
            $this->once()
        )->method(
            "countInstancesOfType"
        )->with(
            $this->equalTo( 23 )
        )->will(
            $this->returnValue( 1 )
        );

        $gatewayMock->expects( $this->never() )->method( "delete" );

        $handler = $this->getHandler();
        $res = $handler->delete( 23, 0 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::createDraft
     *
     * @return void
     */
    public function testCreateVersion()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'createCreateStructFromType' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type'
                )
            )->will(
                $this->returnValue( new CreateStruct() )
            );

        $handlerMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Handler',
            array( 'load', 'internalCreate' ),
            array( $gatewayMock, $mapperMock, $this->getUpdateHandlerMock() )
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
            ->method( 'internalCreate' )
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

        $res = $handlerMock->createDraft(
            42, 23
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::copy
     *
     * @return void
     */
    public function testCopy()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'createCreateStructFromType' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type'
                )
            )->will(
                $this->returnValue( new CreateStruct() )
            );

        $handlerMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Handler',
            array( 'load', 'internalCreate' ),
            array( $gatewayMock, $mapperMock, $this->getUpdateHandlerMock() )
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
            ->method( 'internalCreate' )
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
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::link
     *
     * @return void
     */
    public function testLink()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'insertGroupAssignment' )
            ->with(
                $this->equalTo( 3 ),
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            );

        $mapperMock = $this->getMapperMock();

        $handler = $this->getHandler();
        $res = $handler->link( 3, 23, 1 );

        $this->assertTrue( $res );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::unlink
     *
     * @return void
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
            ->method( 'deleteGroupAssignment' )
            ->with(
                $this->equalTo( 3 ),
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            );

        $mapperMock = $this->getMapperMock();

        $handler = $this->getHandler();
        $res = $handler->unlink( 3, 23, 1 );

        $this->assertTrue( $res );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::unlink
     * @covers eZ\Publish\Core\Persistence\Legacy\Exception\RemoveLastGroupFromType
     * @expectedException eZ\Publish\Core\Persistence\Legacy\Exception\RemoveLastGroupFromType
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

        $handler = $this->getHandler();
        $res = $handler->unlink( 3, 23, 1 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::getFieldDefinition
     *
     * @return void
     */
    public function testGetFieldDefinition()
    {
        $mapperMock = $this->getMapperMock(
            array( 'extractFieldFromRow' )
        );
        $mapperMock->expects( $this->once() )
            ->method( 'extractFieldFromRow' )
            ->with(
                $this->equalTo( array() )
            )->will(
                $this->returnValue( new FieldDefinition() )
            );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'loadFieldDefinition' )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( Type::STATUS_DEFINED )
            )->will(
                $this->returnValue( array() )
            );

        $handler = $this->getHandler();
        $fieldDefinition = $handler->getFieldDefinition( 42, Type::STATUS_DEFINED );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition',
            $fieldDefinition
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::addFieldDefinition
     *
     * @return void
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
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition'
                ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldDefinition'
                )
            );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'insertFieldDefinition' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 ),
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition'
                ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldDefinition'
                )
            )->will(
                $this->returnValue( 42 )
            );

        $fieldDef = new FieldDefinition();

        $handler = $this->getHandler();
        $handler->addFieldDefinition( 23, 1, $fieldDef );

        $this->assertEquals(
            42,
            $fieldDef->id
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::removeFieldDefinition
     *
     * @return void
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

        $handler = $this->getHandler();
        $res = $handler->removeFieldDefinition( 23, 1, 42 );

        $this->assertTrue( $res );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::updateFieldDefinition
     *
     * @return void
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
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition'
                ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldDefinition'
                )
            );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'updateFieldDefinition' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 ),
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition'
                )
            );

        $fieldDef = new FieldDefinition();

        $handler = $this->getHandler();
        $res = $handler->updateFieldDefinition( 23, 1, $fieldDef );

        $this->assertNull( $res );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::publish
     *
     * @return void
     */
    public function testPublish()
    {
        $handler = $this->getPartlyMockedHandler( array( 'load' ) );
        $updateHandlerMock = $this->getUpdateHandlerMock();

        $handler->expects( $this->exactly( 2 ) )
            ->method( 'load' )
            ->with(
                $this->equalTo( 23 ),
                $this->logicalOr(
                    $this->equalTo( 0 ),
                    $this->equalTo( 1 )
                )
            )->will(
                $this->returnValue( new Type() )
            );

        $updateHandlerMock->expects( $this->once() )
            ->method( 'updateContentObjects' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' )
            );
        $updateHandlerMock->expects( $this->once() )
            ->method( 'deleteOldType' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' )
            );
        $updateHandlerMock->expects( $this->once() )
            ->method( 'publishNewType' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' ),
                $this->equalTo( 0 )
            );

        $handler->publish( 23 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler::publish
     *
     * @return void
     */
    public function testPublishNoOldType()
    {
        $handler = $this->getPartlyMockedHandler( array( 'load' ) );
        $updateHandlerMock = $this->getUpdateHandlerMock();

        $handler->expects( $this->at( 0 ) )
            ->method( 'load' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            )->will(
                $this->returnValue( new Type() )
            );

        $handler->expects( $this->at( 1 ) )
            ->method( 'load' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 0 )
            )->will(
                $this->throwException( new Exception\TypeNotFound( 23, 0 ) )
            );

        $updateHandlerMock->expects( $this->never() )
            ->method( 'updateContentObjects' );
        $updateHandlerMock->expects( $this->never() )
            ->method( 'deleteOldType' );
        $updateHandlerMock->expects( $this->once() )
            ->method( 'publishNewType' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' ),
                $this->equalTo( 0 )
            );

        $handler->publish( 23 );
    }

    /**
     * Returns a handler to test, based on mock objects
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler
     */
    protected function getHandler()
    {
        return new Handler(
            $this->getGatewayMock(),
            $this->getMapperMock(),
            $this->getUpdateHandlerMock()
        );
    }

    /**
     * Returns a handler to test with $methods mocked
     *
     * @param array $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler
     */
    protected function getPartlyMockedHandler( array $methods )
    {
        return $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Handler',
            $methods,
            array(
                $this->getGatewayMock(),
                $this->getMapperMock(),
                $this->getUpdateHandlerMock()
            )
        );
    }

    /**
     * Returns a gateway mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Gateway'
            );
        }
        return $this->gatewayMock;
    }

    /**
     * Returns a mapper mock
     *
     * @param array $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper
     */
    protected function getMapperMock( $methods = array() )
    {
        if ( !isset( $this->mapperMock ) )
        {
            $this->mapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Mapper',
                $methods,
                array(),
                '',
                false
            );
        }
        return $this->mapperMock;
    }

    /**
     * Returns a Update\Handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler
     */
    public function getUpdateHandlerMock()
    {
        if ( !isset( $this->updateHandlerMock ) )
        {
            $this->updateHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Update\\Handler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->updateHandlerMock;
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    protected function getContenTypeCreateStructFixture()
    {
        $struct = new CreateStruct();
        $struct->status = 1;
        $struct->groupIds = array(
            42,
        );

        $fieldDefName = new FieldDefinition( array( 'position' => 1 ) );
        $fieldDefShortDescription = new FieldDefinition( array( 'position' => 2 ) );

        $struct->fieldDefinitions = array(
            $fieldDefName,
            $fieldDefShortDescription
        );

        return $struct;
    }
}
