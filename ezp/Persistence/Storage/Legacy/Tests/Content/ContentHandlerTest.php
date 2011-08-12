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
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\Mapper,
    ezp\Persistence\Storage\Legacy\Content\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Location,
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
        $gatewayMock         = $this->getGatewayMock();
        $locationMock        = $this->getLocationHandlerMock();
        $mapperMock          = $this->getMapperMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $handler = new Handler(
            $gatewayMock,
            $locationMock,
            $mapperMock,
            $storageRegistryMock
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
            $storageRegistryMock,
            'storageRegistry',
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
        $mapperMock     = $this->getMapperMock();
        $locationMock   = $this->getLocationHandlerMock();
        $gatewayMock    = $this->getGatewayMock();
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock    = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $handler = new Handler(
            $gatewayMock,
            $locationMock,
            $mapperMock,
            $storageRegMock
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
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\CreateStruct'
                )
            )->will(
                $this->returnValue( new \ezp\Persistence\Content\Location\CreateStruct() )
            );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertContentObject' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' )
            )->will( $this->returnValue( 23 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertVersion' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Version' )
            )->will( $this->returnValue( 1 ) );

        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $storageRegMock->expects( $this->exactly( 2 ) )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'some-type' ) )
            ->will(
                $this->returnValue( $storageMock )
            );

        $storageMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->equalTo( 42 ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\FieldValue'
                )
            );

        $locationMock->expects( $this->once() )
            ->method( 'createLocation' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Location\\CreateStruct'
                ),
                $this->equalTo( 42 )
            );

        $res = $handler->create( $this->getCreateStructFixture() );

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
        $this->assertInternalType(
            'array',
            $res->versionInfos,
            'Version infos not created'
        );
        $this->assertEquals(
            1,
            $res->versionInfos[0]->id,
            'Version ID not set correctly'
        );
        $this->assertEquals(
            2,
            count( $res->versionInfos[0]->fields ),
            'Fields not set correctly in version'
        );
        foreach ( $res->versionInfos[0]->fields as $field )
        {
            $this->assertEquals(
                42,
                $field->id,
                'Field ID not set correctly'
            );
        }
    }

    public static function getLoadedContentBaseData()
    {
        return array(
            array( 'id', 14 ),
            array( 'name', 'Administrator User' ),
            array( 'typeId', 4 ),
            array( 'sectionId', 2 ),
            array( 'ownerId', 14 ),
            array( 'alwaysAvailable', true ),
            array( 'remoteId', '1bb4fe25487f05527efa8bfd394cecc7' ),
        );
    }

    /**
     * @dataProvider getLoadedContentBaseData
     */
    public function testLoadContentBaseData( $property, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );

        $handler = new Handler(
            new Gateway\EzcDatabase( $this->getDatabaseHandler() ),
            new Location\Handler(
                new Location\Gateway\EzcDatabase( $this->getDatabaseHandler() )
            ),
            new Mapper(
                $registry = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry' )
            ),
            new StorageRegistry()
        );

        $converter = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter' );
        $converter
            ->expects( $this->exactly( 5 ) )
            ->method( 'toFieldValue' )
            ->will( $this->returnValue( new FieldValue() ) );

        $registry
            ->expects( $this->exactly( 5 ) )
            ->method( 'getConverter' )
            ->will( $this->returnValue( $converter ) );

        $content = $handler->load( 14, 4 );

        $this->assertEquals( $content->$property, $value );
    }

    public static function getLoadedContentVersionData()
    {
        return array(
            array( 'id', 672 ),
            array( 'versionNo', 4 ),
            array( 'modified', 1311154214 ),
            array( 'creatorId', 14 ),
            array( 'created', 1311154214 ),
            array( 'state', 1 ),
            array( 'contentId', 14 ),
        );
    }

    /**
     * @dataProvider getLoadedContentVersionData
     */
    public function testLoadContentVersionData( $property, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );

        $handler = new Handler(
            new Gateway\EzcDatabase( $this->getDatabaseHandler() ),
            new Location\Handler(
                new Location\Gateway\EzcDatabase( $this->getDatabaseHandler() )
            ),
            new Mapper(
                $registry = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry' )
            ),
            new StorageRegistry()
        );

        $converter = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter' );
        $converter
            ->expects( $this->exactly( 5 ) )
            ->method( 'toFieldValue' )
            ->will( $this->returnValue( new FieldValue() ) );

        $registry
            ->expects( $this->exactly( 5 ) )
            ->method( 'getConverter' )
            ->will( $this->returnValue( $converter ) );

        $content = $handler->load( 14, 4 );

        $this->assertEquals( $content->versionInfos[0]->$property, $value );
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \ezp\Persistence\Content\CreateStruct
     */
    public function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $firstField        = new Field();
        $firstField->type  = 'some-type';
        $firstField->value = new FieldValue();

        $secondField = clone $firstField;

        $struct->fields = array(
            $firstField, $secondField
        );

        $struct->parentLocations = array( 42 );

        return $struct;
    }

    /**
     * Returns a StorageRegistry mock.
     *
     * @return StorageRegistry
     */
    protected function getStorageRegistryMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageRegistry',
            array( 'getStorage' )
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
    protected function getLocationHandlerMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Handler',
            array( 'createLocation' ),
            array(),
            '',
            false
        );
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected function getGatewayMock()
    {
        return $this->getMockForAbstractClass(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Gateway'
        );
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
