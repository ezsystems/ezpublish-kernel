<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\RestrictedVersion,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\Mapper,
    ezp\Persistence\Storage\Legacy\Content\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Location,
    ezp\Persistence\Storage\Legacy\Content\Type,
    ezp\Persistence\Storage\Legacy\Content\StorageRegistry,
    ezp\Persistence\Storage\Legacy\Content\Handler;

/**
 * Test case for Content Handler
 */
class ContentHandlerTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::__construct
     */
    public function testCtor()
    {
        // @TODO: Extract to dedicated method
        $gatewayMock = $this->getGatewayMock();
        $locationMock = $this->getLocationGatewayMock();
        $typeGatewayMock = $this->getTypeGatewayMock();
        $mapperMock = $this->getMapperMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $handler = new Handler(
            $gatewayMock,
            $locationMock,
            $typeGatewayMock,
            $mapperMock,
            $storageHandlerMock
        );

        $this->assertAttributeSame(
            $gatewayMock,
            'contentGateway',
            $handler
        );
        $this->assertAttributeSame(
            $mapperMock,
            'mapper',
            $handler
        );
        $this->assertAttributeSame(
            $storageHandlerMock,
            'storageHandler',
            $handler
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::create
     * @todo Current method way to complex to test, refactor!
     */
    public function testCreate()
    {
        // @TODO: Extract to dedicated method
        $mapperMock = $this->getMapperMock();
        $locationMock = $this->getLocationGatewayMock();
        $gatewayMock = $this->getGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $handler = new Handler(
            $gatewayMock,
            $locationMock,
            $this->getTypeGatewayMock(),
            $mapperMock,
            $storageHandlerMock
        );

        $mapperMock->expects( $this->once() )
            ->method( 'createContentFromCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\CreateStruct'
                )
            )->will(
                $this->returnValue( new Content() )
            );
        $mapperMock->expects( $this->once() )
            ->method( 'createVersionForContent' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content'
                )
            )->will(
                $this->returnValue( new Version() )
            );
        $mapperMock->expects( $this->exactly( 2 ) )
            ->method( 'convertToStorageValue' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Field'
                )
            )->will(
                $this->returnValue( new StorageFieldValue() )
            );
        $mapperMock->expects( $this->once() )
            ->method( 'createLocationCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content'
                )
            )->will(
                $this->returnValue( new \ezp\Persistence\Content\Location\CreateStruct() )
            );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertContentObject' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' ),
                $this->isType( 'array' )
            )->will( $this->returnValue( 23 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertVersion' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Version' ),
                $this->isType( 'array' )
            )->will( $this->returnValue( 1 ) );

        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) );

        $locationMock->expects( $this->once() )
            ->method( 'createNodeAssignment' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Location\\CreateStruct'
                ),
                $this->equalTo( 42 )
            );

        $res = $handler->create( $this->getCreateStructFixture() );

        // @TODO Make subsequent tests

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content',
            $res,
            'Content not created'
        );
        $this->assertEquals(
            23,
            $res->id,
            'Content ID not set correctly'
        );
        $this->assertInstanceOf(
            '\\ezp\\Persistence\\Content\\Version',
            $res->version,
            'Version infos not created'
        );
        $this->assertEquals(
            1,
            $res->version->id,
            'Version ID not set correctly'
        );
        $this->assertEquals(
            2,
            count( $res->version->fields ),
            'Fields not set correctly in version'
        );
        foreach ( $res->version->fields as $field )
        {
            $this->assertEquals(
                42,
                $field->id,
                'Field ID not set correctly'
            );
        }
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::publish
     */
    public function testPublish()
    {
        // @TODO: Extract to dedicated method
        $mapperMock = $this->getMapperMock();
        $locationMock = $this->getLocationGatewayMock();
        $typeGatewayMock = $this->getTypeGatewayMock();
        $gatewayMock = $this->getGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $handler = $this->getMock(
            '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            array( 'update' ),
            array(
                $gatewayMock,
                $locationMock,
                $typeGatewayMock,
                $mapperMock,
                $storageHandlerMock
            )
        );

        $updateStruct = new UpdateStruct(
            array(
                'id' => 42,
                'versionNo' => 1,
                'name' => array(
                    'eng-US' => "Hello",
                    'eng-GB' => "Hello (GB)",
                ),
            )
        );

        $handler
            ->expects( $this->once() )
            ->method( 'update' )
            ->with( $updateStruct );

        $gatewayMock
            ->expects( $this->at( 0 ) )
            ->method( 'setName' )
            ->with( 42, 1, 'Hello', 'eng-US' );

        $gatewayMock
            ->expects( $this->at( 1 ) )
            ->method( 'setName' )
            ->with( 42, 1, 'Hello (GB)', 'eng-GB' );

        $locationMock
            ->expects( $this->at( 0 ) )
            ->method( 'createLocationsFromNodeAssignments' )
            ->with( 42, 1 );

        $handler->publish( $updateStruct );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::update
     */
    public function testUpdateContent()
    {
        // Build up basic mocks
        $mapper = new Mapper(
            $locationMapperMock = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Mapper' ),
            $registry = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry' )
        );

        $converter = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter' );
        $converter
            ->expects( $this->any() )
            ->method( 'toStorage' )
            ->will( $this->returnValue( new StorageFieldValue() ) );

        $registry
            ->expects( $this->any() )
            ->method( 'getConverter' )
            ->will( $this->returnValue( $converter ) );

        $locationMock = $this->getLocationGatewayMock();
        $typeGatewayMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway'
        );
        $gatewayMock = $this->getGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $handler = $this->getMock(
            '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            array( 'load' ),
            array(
                $gatewayMock,
                $locationMock,
                $typeGatewayMock,
                $mapper,
                $storageHandlerMock
            )
        );

        // Ensure the external storage handler is called properly.
        $storageHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' )
            );

        // These are the actually important expectations -- ensuring the
        // correct methods are called on the mapper.
        $gatewayMock->expects( $this->once() )
            ->method( 'updateVersion' )
            ->with( 14, 4 );

        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'updateNonTranslatableField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue' )
            );

        $handler->expects( $this->at( 0 ) )
            ->method( 'load' )
            ->with( 14, 4 );

        $result = $handler->update(
            new UpdateStruct(
                array(
                    'id' => 14,
                    'versionNo' => 4,
                    'creatorId' => 14,
                    'ownerId' => 14,
                    'fields' => array(
                        new Field(
                            array(
                                'id' => 23,
                                'fieldDefinitionId' => 42,
                                'type' => 'some-type',
                                'value' => new FieldValue(),
                            )
                        ),
                        new Field(
                            array(
                                'id' => 23,
                                'fieldDefinitionId' => 43,
                                'type' => 'some-type',
                                'value' => new FieldValue(),
                            )
                        ),
                    )
                )
            )
        );
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \ezp\Persistence\Content\CreateStruct
     */
    public function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $firstField = new Field();
        $firstField->type = 'some-type';
        $firstField->value = new FieldValue();

        $secondField = clone $firstField;

        $struct->fields = array(
            $firstField, $secondField
        );

        $struct->parentLocations = array( 42 );

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::listVersions
     */
    public function testListVersions()
    {
        $handler = new Handler(
            ( $gatewayMock = $this->getGatewayMock() ),
            $this->getLocationGatewayMock(),
            $this->getTypeGatewayMock(),
            ( $mapperMock = $this->getMapperMock() ),
            $this->getStorageHandlerMock()
        );

        $gatewayMock->expects( $this->once() )
            ->method( 'listVersions' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array() ) );

        $mapperMock->expects( $this->once() )
            ->method( 'extractVersionListFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new RestrictedVersion() ) ) );

        $res = $handler->listVersions( 23 );

        $this->assertEquals(
            array( new RestrictedVersion() ),
            $res
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::delete
     */
    public function testDelete()
    {
        $handler = new Handler(
            ( $gatewayMock = $this->getGatewayMock() ),
            ( $locationHandlerMock = $this->getLocationGatewayMock() ),
            $this->getTypeGatewayMock(),
            $this->getMapperMock(),
            ( $storageHandlerMock = $this->getStorageHandlerMock() )
        );

        $gatewayMock->expects( $this->once() )
            ->method( 'getAllLocationIds' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array( 42, 24 ) ) );

        $locationHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'removeSubtree' )
            ->with(
                $this->logicalOr(
                    $this->equalTo( 42 ),
                    $this->equalTo( 24 )
                )
            );

        $gatewayMock->expects( $this->once() )
            ->method( 'getFieldIdsByType' )
            ->with( $this->equalTo( 23 ) )
            ->will(
                $this->returnValue(
                    array( 'ezstring' => array( 1, 2 ), 'ezuser' => array( 3 ) )
                )
            );
        $storageHandlerMock->expects( $this->at( 0 ) )
            ->method( 'deleteFieldData' )
            ->with( $this->equalTo( 'ezstring' ), array( 1, 2 ) );
        $storageHandlerMock->expects( $this->at( 1 ) )
            ->method( 'deleteFieldData' )
            ->with( $this->equalTo( 'ezuser' ), array( 3 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'deleteRelations' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteFields' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteVersions' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteNames' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteContent' )
            ->with( $this->equalTo( 23 ) );

        $handler->delete( 23 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::createCopy
     */
    public function testCreateCopy()
    {
        $handler = $this->getMock(
            'ezp\Persistence\Storage\Legacy\Content\Handler',
            array( 'create' ),
            array(
                ( $gatewayMock = $this->getGatewayMock() ),
                $this->getLocationGatewayMock(),
                $this->getTypeGatewayMock(),
                ( $mapperMock = $this->getMapperMock() ),
                ( $storageHandlerMock = $this->getStorageHandlerMock() )
            )
        );

        $gatewayMock->expects( $this->once() )
            ->method( 'loadLatestPublishedData' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array( 0 => array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( $this->isType( 'array' ) )
            ->will( $this->returnValue( array( new Content() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createCreateStructFromContent' )
            ->with( $this->isInstanceOf(
                'ezp\\Persistence\\Content'
            ) )->will(
                $this->returnValue( new CreateStruct() )
            );

        $handler->expects( $this->once() )
            ->method( 'create' )
            ->with( $this->isInstanceOf(
                'ezp\\Persistence\\Content\\CreateStruct'
            ) )->will( $this->returnValue( new Content() ) );

        $result = $handler->createCopy( 23 );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content',
            $result
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::setStatus
     */
    public function testSetStatus()
    {
        $handler = new Handler(
            ( $gatewayMock = $this->getGatewayMock() ),
            $this->getLocationGatewayMock(),
            $this->getTypeGatewayMock(),
            ( $mapperMock = $this->getMapperMock() ),
            $this->getStorageHandlerMock()
        );

        $gatewayMock->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( 23, 5, 2 )
            ->will( $this->returnValue( true ) );

        $this->assertTrue(
            $handler->setStatus( 23, 2, 5 )
        );
    }

    /**
     * Returns a StorageHandler mock.
     *
     * @return StorageHandler
     */
    protected function getStorageHandlerMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageHandler',
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * Returns a Mapper mock.
     *
     * @return Mapper
     */
    protected function getMapperMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Mapper',
            array(),
            array(),
            '',
            false
        );
    }

    /**
     * Returns a Location handler mock
     *
     * @return Mapper
     */
    protected function getLocationGatewayMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Gateway'
        );
    }

    /**
     * Returns a Content Type gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected function getTypeGatewayMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway'
        );
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected function getGatewayMock()
    {
        $mock = $this->getMockForAbstractClass(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Gateway'
        );

        $mock
            ->expects( $this->any() )
            ->method( 'getContext' )
            ->will( $this->returnValue( array() ) );

        return $mock;
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
