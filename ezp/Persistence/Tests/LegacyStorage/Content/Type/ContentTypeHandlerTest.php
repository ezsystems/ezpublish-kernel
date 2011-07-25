<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegcyStorage\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,

    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\GroupUpdateStruct,

    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler,
    ezp\Persistence\LegacyStorage\Content\Type\Mapper,
    ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway;

/**
 * Test case for ContentTypeHandler.
 */
class ContentTypeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::__construct
     */
    public function testCtor()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapper = new Mapper();
        $handler = new ContentTypeHandler( $gatewayMock, $mapper );

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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::createGroup
     */
    public function testCreateGroup()
    {
        $createStruct = new GroupCreateStruct();

        $mapperMock = $this->getMock(
            'ezp\Persistence\LegacyStorage\Content\Type\Mapper',
            array( 'createGroupFromCreateStruct' )
        );
        $mapperMock->expects( $this->once() )
            ->method( 'createGroupFromCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'ezp\Persistence\Content\Type\Group\GroupCreateStruct'
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
                    'ezp\Persistence\Content\Type\Group'
                )
            )
            ->will( $this->returnValue( 23 ) );

        $handler = new ContentTypeHandler( $gatewayMock, $mapperMock );
        $group = $handler->createGroup(
            new GroupCreateStruct()
        );

        $this->assertInstanceOf(
            'ezp\Persistence\Content\Type\Group',
            $group
        );
        $this->assertEquals(
            23,
            $group->id
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::updateGroup
     */
    public function testUpdateGroup()
    {
        $createStruct = new GroupUpdateStruct();

        $mapperMock = $this->getMock(
            'ezp\Persistence\LegacyStorage\Content\Type\Mapper',
            array( 'createGroupFromCreateStruct' )
        );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'updateGroup' )
            ->with(
                $this->isInstanceOf(
                    'ezp\Persistence\Content\Type\Group\GroupUpdateStruct'
                )
            );

        $handler = new ContentTypeHandler( $gatewayMock, $mapperMock );
        $res = $handler->updateGroup(
            new GroupUpdateStruct()
        );

        $this->assertSame(
            true,
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::load
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

        $mapperMock = $this->getMock(
            'ezp\Persistence\LegacyStorage\Content\Type\Mapper',
            array( 'extractTypesFromRows' )
        );
        $mapperMock->expects( $this->once() )
            ->method( 'extractTypesFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will(
                $this->returnValue(
                    array( new Type() )
                )
            );

        $handler = new ContentTypeHandler( $gatewayMock, $mapperMock );
        $type = $handler->load( 23, 1 );

        $this->assertEquals(
            new Type(),
            $type,
            'Type not loaded correctly'
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::create
     */
    public function testCreate()
    {
        $createStructFix = $this->getContenTypeCreateStructFixture();
        $createStructClone = clone $createStructFix;

        $gatewayMock = $this->getMockForAbstractClass(
            'ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway'
        );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertType' )
            ->with(
                $this->isInstanceOf(
                    'ezp\Persistence\Content\Type'
                )
            )
            ->will( $this->returnValue( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'insertGroupAssignement' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 1 )
            );
        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertFieldDefinition' )
            ->will( $this->returnValue( 42 ) );

        $handler = new ContentTypeHandler( $gatewayMock, new Mapper() );
        $type = $handler->create( $createStructFix );

        $this->assertInstanceOf(
            'ezp\Persistence\Content\Type',
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
     * @covers ezp\Persistence\LegacyStorage\Content\Type\ContentTypeHandler::delete
     */
    public function testDelete()
    {
        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteFieldDefinitionsForType' )
            ->with( $this->equalTo( 23, 0 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteType' )
            ->with( $this->equalTo( 23, 0 ) );

        $mapperMock = $this->getMock(
            'ezp\Persistence\LegacyStorage\Content\Type\Mapper'
        );

        $handler = new ContentTypeHandler( $gatewayMock, $mapperMock );
        $res = $handler->delete( 23, 0 );

        $this->assertTrue( $res );
    }

    /**
     * Returns a gateway mock
     *
     * @return ContentTypeGateway
     */
    protected function getGatewayMock()
    {
        return $this->getMockForAbstractClass(
            'ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway'
        );
    }

    /**
     * Returns a ContentTypeCreateStruct fixture.
     *
     * @return ContentTypeCreateStruct
     */
    protected function getContenTypeCreateStructFixture()
    {
        $struct = new ContentTypeCreateStruct();
        $struct->contentTypeGroupIds = array(
            1,
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
