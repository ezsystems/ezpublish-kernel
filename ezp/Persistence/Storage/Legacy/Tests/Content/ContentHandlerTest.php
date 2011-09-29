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
     * Content handler to test
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $gatewayMock;

    /**
     * Location gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected $locationGatewayMock;

    /**
     * Type gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected $typeGatewayMock;

    /**
     * Mapper mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected $mapperMock;

    /**
     * Storage handler mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\StorageHandler
     */
    protected $storageHandlerMock;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::__construct
     */
    public function testCtor()
    {
        $handler = $this->getContentHandler();

        $this->assertAttributeSame(
            $this->getGatewayMock(),
            'contentGateway',
            $handler
        );
        $this->assertAttributeSame(
            $this->getMapperMock(),
            'mapper',
            $handler
        );
        $this->assertAttributeSame(
            $this->getStorageHandlerMock(),
            'storageHandler',
            $handler
        );
        // @TODO Assert missing ptoperties
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::create
     * @todo Current method way to complex to test, refactor!
     */
    public function testCreate()
    {
        $handler = $this->getContentHandler();

        $mapperMock         = $this->getMapperMock();
        $gatewayMock        = $this->getGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();
        $locationMock       = $this->getLocationGatewayMock();

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
        $handler = $this->getPartlyMockedHandler( array( 'update' ) );

        $gatewayMock  = $this->getGatewayMock();
        $locationMock = $this->getLocationGatewayMock();

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
        // @TODO Fix test case to not depend on nested deps

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
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock  = $this->getMapperMock();

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
        $handler = $this->getContentHandler();

        $gatewayMock         = $this->getGatewayMock();
        $locationHandlerMock = $this->getLocationGatewayMock();
        $storageHandlerMock  = $this->getStorageHandlerMock();

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
        $handler = $this->getPartlyMockedHandler( array( 'create' ) );

        $gatewayMock        = $this->getGatewayMock();
        $mapperMock         = $this->getMapperMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

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
        $handler = $this->getContentHandler();

        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( 23, 5, 2 )
            ->will( $this->returnValue( true ) );

        $this->assertTrue(
            $handler->setStatus( 23, 2, 5 )
        );
    }

    /**
     * Returns the handler to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Handler
     */
    protected function getContentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new Handler(
                $this->getGatewayMock(),
                $this->getLocationGatewayMock(),
                $this->getTypeGatewayMock(),
                $this->getMapperMock(),
                $this->getStorageHandlerMock()
            );
        }
        return $this->contentHandler;
    }

    /**
     * Returns the handler to test with $methods mocked
     *
     * @param string[] $methods
     * @return \ezp\Persistence\Storage\Legacy\Content\Handler
     */
    protected function getPartlyMockedHandler( array $methods )
    {
        return $this->getMock(
            '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            $methods,
            array(
                $this->getGatewayMock(),
                $this->getLocationGatewayMock(),
                $this->getTypeGatewayMock(),
                $this->getMapperMock(),
                $this->getStorageHandlerMock()
            )
        );
    }

    /**
     * Returns a StorageHandler mock.
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\StorageHandler
     */
    protected function getStorageHandlerMock()
    {
        if ( !isset( $this->storageHandlerMock ) )
        {
            $this->storageHandlerMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageHandler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->storageHandlerMock;
    }

    /**
     * Returns a Mapper mock.
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected function getMapperMock()
    {
        if ( !isset( $this->mapperMock ) )
        {
            $this->mapperMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mapperMock;
    }

    /**
     * Returns a Location Gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected function getLocationGatewayMock()
    {
        if ( !isset( $this->locationGatewayMock ) )
        {
            $this->locationGatewayMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Gateway'
            );
        }
        return $this->locationGatewayMock;
    }

    /**
     * Returns a Content Type gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected function getTypeGatewayMock()
    {
        if ( !isset( $this->typeGatewayMock ) )
        {
            $this->typeGatewayMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway'
            );
        }
        return $this->typeGatewayMock;
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Gateway'
            );
        }
        return $this->gatewayMock;
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
