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
            ->method( 'create' )
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

    protected function getAlmostRealContentHandler()
    {
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
            ->expects( $this->any() )
            ->method( 'toFieldValue' )
            ->will( $this->returnValue( new FieldValue() ) );

        $registry
            ->expects( $this->any() )
            ->method( 'getConverter' )
            ->will( $this->returnValue( $converter ) );

        return $handler;
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
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::load
     */
    public function testLoadContentBaseData( $property, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );
        $handler = $this->getAlmostRealContentHandler();

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
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::load
     */
    public function testLoadContentVersionData( $property, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );
        $handler = $this->getAlmostRealContentHandler();

        $content = $handler->load( 14, 4 );

        $this->assertEquals( $content->version->$property, $value );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::load
     */
    public function testLoadContentFieldDataFiledTypes()
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
            ->expects( $this->at( 0 ) )
            ->method( 'getConverter' )
            ->with( 'ezstring' )
            ->will( $this->returnValue( $converter ) );

        $registry
            ->expects( $this->at( 1 ) )
            ->method( 'getConverter' )
            ->with( 'ezstring' )
            ->will( $this->returnValue( $converter ) );

        $registry
            ->expects( $this->at( 2 ) )
            ->method( 'getConverter' )
            ->with( 'ezuser' )
            ->will( $this->returnValue( $converter ) );

        $registry
            ->expects( $this->at( 3 ) )
            ->method( 'getConverter' )
            ->with( 'eztext' )
            ->will( $this->returnValue( $converter ) );

        $registry
            ->expects( $this->at( 4 ) )
            ->method( 'getConverter' )
            ->with( 'ezimage' )
            ->will( $this->returnValue( $converter ) );

        $content = $handler->load( 14, 4 );
    }

    public static function getLoadedContentFieldData()
    {
        return array(
            array( 'id', 28 ),
            array( 'fieldDefinitionId', 8 ),
            array( 'type', 'ezstring' ),
            array( 'language', 'eng-US' ),
            array( 'versionNo', 4 ),
        );
    }

    /**
     * @dataProvider getLoadedContentFieldData
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::load
     */
    public function testLoadContentFieldData( $property, $value )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );
        $handler = $this->getAlmostRealContentHandler();

        $content = $handler->load( 14, 4 );

        $this->assertEquals( $content->version->fields[0]->$property, $value );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::load
     */
    public function testLoadContentLocations()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );
        $handler = $this->getAlmostRealContentHandler();

        $content = $handler->load( 14, 4 );

        $this->assertEquals(
            array( 15 ),
            $content->locations
        );
    }

    protected function getTestCreateDraftFromVersion()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );
        $handler = $this->getAlmostRealContentHandler();
        $content = $handler->load( 14, 4 );

        // Build up basic mocks
        $mapper = new Mapper(
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

        $locationMock   = $this->getLocationHandlerMock();
        $gatewayMock    = $this->getGatewayMock();
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock    = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $handler = $this->getMock( 'ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            array( 'load' ),
            array( $gatewayMock, $locationMock, $mapper, $storageRegMock )
        );

        // Handler expects load() to be called on itself, where we return a "proper"
        // content object.
        $handler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( 14, 4 )
            ->will( $this->returnValue( $content ) );

        // Ensure the external storage handler is called properly.
        $storageRegMock->expects( $this->exactly( 5 ) )
            ->method( 'getStorage' )
            ->will(
                $this->returnValue( $storageMock )
            );

        $storageMock->expects( $this->exactly( 5 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->equalTo( 23 ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\FieldValue'
                )
            );

        // These are the actually important expectations -- ensuring the
        // correct methods are called on the mapper.
        $gatewayMock->expects( $this->once() )
            ->method( 'insertVersion' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Version' )
            )->will( $this->returnValue( 2 ) );

        $gatewayMock->expects( $this->exactly( 5 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 23 ) );

        return $handler->createDraftFromVersion( 14, 4 );
    }

    public static function getCreateDraftFromVersionProperties()
    {
        return array(
            array( 'id', 14 ),
            array( 'name', 'Administrator User' ),
            array( 'typeId', 4 ),
            array( 'sectionId', 2 ),
            array( 'ownerId', 14 ),
            array( 'locations', array( 15 ) ),
            array( 'alwaysAvailable', true ),
            array( 'remoteId', '1bb4fe25487f05527efa8bfd394cecc7' ),
        );
    }

    /**
     * @dataProvider getCreateDraftFromVersionProperties
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::createDraftFromVersion
     */
    public function testCreateDraftFromVersion( $property, $expectation )
    {
        $content = $this->getTestCreateDraftFromVersion();

        $this->assertEquals(
            $expectation,
            $content->$property
        );
    }

    public static function getCreateDraftFromVersionVersionProperties()
    {
        return array(
            array( 'id', 2 ),
            array( 'versionNo', 5 ),
            array( 'creatorId', 14 ),
            array( 'state', 0 ),
            array( 'contentId', 14 ),
        );
    }

    /**
     * @dataProvider getCreateDraftFromVersionVersionProperties
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::createDraftFromVersion
     */
    public function testCreateDraftFromVersionVersionProperties( $property, $expectation )
    {
        $content = $this->getTestCreateDraftFromVersion();

        $this->assertEquals(
            $expectation,
            $content->version->$property
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::createDraftFromVersion
     */
    public function testCreateDraftFromVersionFields()
    {
        $content = $this->getTestCreateDraftFromVersion();

        foreach ( $content->version->fields as $field )
        {
            $this->assertEquals( 23, $field->id );
            $this->assertEquals( 5, $field->versionNo );
        }
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::update
     */
    public function testUpdateContent()
    {
        // Build up basic mocks
        $mapper = new Mapper(
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

        $locationMock   = $this->getLocationHandlerMock();
        $gatewayMock    = $this->getGatewayMock();
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock    = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $handler = new Handler( $gatewayMock, $locationMock, $mapper, $storageRegMock );

        // Ensure the external storage handler is called properly.
        $storageRegMock->expects( $this->exactly( 2 ) )
            ->method( 'getStorage' )
            ->will(
                $this->returnValue( $storageMock )
            );

        $storageMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->equalTo( 23 ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\FieldValue'
                )
            );

        // These are the actually important expectations -- ensuring the
        // correct methods are called on the mapper.
        $gatewayMock->expects( $this->once() )
            ->method( 'updateVersion' )
            ->with( 14, 4, 14 );

        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'updateField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue' )
            );

        $result = $handler->update( new UpdateStruct( array(
            'id' => 14,
            'versionNo' => 4,
            'userId' => 14,
            'fields' => array(
                new Field( array(
                    'id' => 23,
                    'fieldDefinitionId' => 42,
                    'type' => 'some-type',
                    'value' => new FieldValue(),
                ) ),
                new Field( array(
                    'id' => 23,
                    'fieldDefinitionId' => 43,
                    'type' => 'some-type',
                    'value' => new FieldValue(),
                ) ),
            )
        ) ) );
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
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::listVersions
     */
    public function testListVersions()
    {
        $handler = new Handler(
            ( $gatewayMock = $this->getGatewayMock() ),
            $this->getLocationHandlerMock(),
            ( $mapperMock = $this->getMapperMock() ),
            $this->getStorageRegistryMock()
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
            array( 'create' ),
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
