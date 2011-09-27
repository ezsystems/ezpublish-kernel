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
        $gatewayMock = $this->getGatewayMock();
        $locationMock = $this->getLocationGatewayMock();
        $typeGatewayMock = $this->getTypeGatewayMock();
        $mapperMock = $this->getMapperMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $handler = new Handler(
            $gatewayMock,
            $locationMock,
            $typeGatewayMock,
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
        $mapperMock = $this->getMapperMock();
        $locationMock = $this->getLocationGatewayMock();
        $gatewayMock = $this->getGatewayMock();
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $handler = new Handler(
            $gatewayMock,
            $locationMock,
            $this->getTypeGatewayMock(),
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

        $storageRegMock->expects( $this->exactly( 2 ) )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'some-type' ) )
            ->will(
                $this->returnValue( $storageMock )
            );

        $storageMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Field'
                )
            );

        $locationMock->expects( $this->once() )
            ->method( 'createNodeAssignment' )
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

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::publish
     */
    public function testPublish()
    {
        $mapperMock = $this->getMapperMock();
        $locationMock = $this->getLocationGatewayMock();
        $typeGatewayMock = $this->getTypeGatewayMock();
        $gatewayMock = $this->getGatewayMock();
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $handler = $this->getMock(
            '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            array( 'update' ),
            array(
                $gatewayMock,
                $locationMock,
                $typeGatewayMock,
                $mapperMock,
                $storageRegMock
            )
        );

        $updateStruct = new UpdateStruct( array(
            'id' => 42,
            'versionNo' => 1,
            'name' => array(
                'eng-US' => "Hello",
                'eng-GB' => "Hello (GB)",
            ),
        ) );

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
     * Returns a content handler with rather real instead of mock objects.
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Handler
     */
    protected function getAlmostRealContentHandler()
    {
        $handler = new Handler(
            new Gateway\EzcDatabase(
                $this->getDatabaseHandler(),
                new Gateway\EzcDatabase\QueryBuilder( $this->getDatabaseHandler() ),
                $this->getLanguageHandlerMock(),
                $this->getLanguageMaskGeneratorMock()
            ),
            new Location\Gateway\EzcDatabase( $this->getDatabaseHandler() ),
            $this->getMock( 'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway' ),
            new Mapper(
                new Location\Mapper(),
                $registry = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry' )
            ),
            $storageRegMock = $this->getStorageRegistryMock()
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

        $storageMock = $this->getMock( 'ezp\\Persistence\\Fields\\Storage' );

        $storageRegMock
            ->expects( $this->any() )
            ->method( 'getStorage' )
            ->will(
                $this->returnValue( $storageMock )
            );

        return $handler;
    }

    /**
     * Returns a handler mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\CachingLanguageHandler
     */
    protected function getLanguageHandlerMock()
    {
        $mock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Language\\CachingHandler',
            array(),
            array(),
            '',
            false
        );

        return $mock;
    }

    /**
     * Returns a language mask generator mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageMaskGeneratorMock()
    {
        $mock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Language\\MaskGenerator',
            array(),
            array(),
            '',
            false
        );
        $mock->expects( $this->any() )
            ->method( 'generateLanguageMask' )
            ->will( $this->returnValue( 3 ) );

        return $mock;
    }

    /**
     * Returns base reference data for loaded content.
     *
     * Data provider for {@link testLoadContentBaseData()}.
     *
     * @return mixed[][]
     */
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
     * @return void
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

    /**
     * Returns version reference data for loaded content
     *
     * Data provider for {@link testLoadContentVersionData()}.
     *
     * @return mixed[][]
     */
    public static function getLoadedContentVersionData()
    {
        return array(
            array( 'id', 672 ),
            array( 'versionNo', 4 ),
            array( 'modified', 1311154214 ),
            array( 'creatorId', 14 ),
            array( 'created', 1311154214 ),
            array( 'status', 1 ),
            array( 'contentId', 14 ),
        );
    }

    /**
     * @return void
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
            new Gateway\EzcDatabase(
                $this->getDatabaseHandler(),
                new Gateway\EzcDatabase\QueryBuilder( $this->getDatabaseHandler() ),
                $this->getLanguageHandlerMock(),
                $this->getLanguageMaskGeneratorMock()
            ),
            new Location\Gateway\EzcDatabase( $this->getDatabaseHandler() ),
            $this->getMock( 'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway' ),
            new Mapper(
                $locationMapperMock = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Mapper' ),
                $registry = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry' )
            ),
            $storageRegMock = $this->getStorageRegistryMock()
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

        $storageMock = $this->getMock( 'ezp\\Persistence\\Fields\\Storage' );

        $storageRegMock
            ->expects( $this->any( 0 ) )
            ->method( 'getStorage' )
            ->will( $this->returnValue( $storageMock ) );

        $content = $handler->load( 14, 4 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::load
     */
    public function testLoadContentFieldDataGetFieldData()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );

        $handler = new Handler(
            new Gateway\EzcDatabase(
                $this->getDatabaseHandler(),
                new Gateway\EzcDatabase\QueryBuilder( $this->getDatabaseHandler() ),
                $this->getLanguageHandlerMock(),
                $this->getLanguageMaskGeneratorMock()
            ),
            new Location\Gateway\EzcDatabase( $this->getDatabaseHandler() ),
            $this->getMock( 'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway' ),
            new Mapper(
                $locationMapperMock = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Mapper' ),
                $registry = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry' )
            ),
            $storageRegMock = $this->getStorageRegistryMock()
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

        $storageMock = $this->getMock( 'ezp\\Persistence\\Fields\\Storage' );

        $storageRegMock
            ->expects( $this->at( 0 ) )
            ->method( 'getStorage' )
            ->with( 'ezstring' )
            ->will( $this->returnValue( $storageMock ) );

        $storageRegMock
            ->expects( $this->at( 1 ) )
            ->method( 'getStorage' )
            ->with( 'ezstring' )
            ->will( $this->returnValue( $storageMock ) );

        $storageRegMock
            ->expects( $this->at( 2 ) )
            ->method( 'getStorage' )
            ->with( 'ezuser' )
            ->will( $this->returnValue( $storageMock ) );

        $storageRegMock
            ->expects( $this->at( 3 ) )
            ->method( 'getStorage' )
            ->with( 'eztext' )
            ->will( $this->returnValue( $storageMock ) );

        $storageRegMock
            ->expects( $this->at( 4 ) )
            ->method( 'getStorage' )
            ->with( 'ezimage' )
            ->will( $this->returnValue( $storageMock ) );

        $storageMock
            ->expects( $this->at( 0 ) )
            ->method( 'hasFieldData' )
            ->will( $this->returnValue( false ) );

        $storageMock
            ->expects( $this->at( 1 ) )
            ->method( 'hasFieldData' )
            ->will( $this->returnValue( false ) );

        $storageMock
            ->expects( $this->at( 2 ) )
            ->method( 'hasFieldData' )
            ->will( $this->returnValue( true ) );

        $storageMock
            ->expects( $this->at( 3 ) )
            ->method( 'getFieldData' )
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) )
            ->will( $this->returnValue( true ) );

        $storageMock
            ->expects( $this->at( 4 ) )
            ->method( 'hasFieldData' )
            ->will( $this->returnValue( false ) );

        $storageMock
            ->expects( $this->at( 5 ) )
            ->method( 'hasFieldData' )
            ->will( $this->returnValue( false ) );

        $content = $handler->load( 14, 4 );
    }

    /**
     * Returns content field reference data for loaded content.
     *
     * Data provider for {@link testLoadContentFieldData()}.
     *
     * @return mixed[][]
     */
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
            1,
            count( $content->locations )
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Location',
            $content->locations[0]
        );
        $this->assertEquals(
            15,
            $content->locations[0]->id
        );
    }

    /**
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadContentNotFound()
    {
        $handler = $this->getAlmostRealContentHandler();

        $content = $handler->load( 1337, 4 );
    }

    /**
     * Returns a result from creating a draft from a version.
     *
     * @return \ezp\Persistence\Content
     */
    protected function getTestCreateDraftFromVersion()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/contentobjects.php' );
        $handler = $this->getAlmostRealContentHandler();
        $content = $handler->load( 14, 4 );

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
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $handler = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            array( 'load' ),
            array( $gatewayMock, $locationMock, $typeGatewayMock, $mapper, $storageRegMock )
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
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Field'
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

    /**
     * Returns base reference data for drafted content.
     *
     * Data provider for {@link testCreateDraftFromVersion()}.
     *
     * @return mixed[][]
     */
    public static function getCreateDraftFromVersionProperties()
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

    /**
     * Returns version reference data for drafted content.
     *
     * Data provider for {@link testCreateDraftFromVersionVersionProperties()}.
     *
     * @return mixed[][]
     */
    public static function getCreateDraftFromVersionVersionProperties()
    {
        return array(
            array( 'id', 2 ),
            array( 'versionNo', 5 ),
            array( 'creatorId', 14 ),
            array( 'status', 0 ),
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
    public function testCreateDraftFromVersionLocation()
    {
        $content = $this->getTestCreateDraftFromVersion();

        $this->assertEquals(
            1,
            count( $content->locations )
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Location',
            $content->locations[0]
        );
        $this->assertEquals(
            15,
            $content->locations[0]->id
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
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $handler = $this->getMock(
            '\\ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            array( 'load' ),
            array(
                $gatewayMock,
                $locationMock,
                $typeGatewayMock,
                $mapper,
                $storageRegMock
            )
        );

        // Ensure the external storage handler is called properly.
        $storageRegMock->expects( $this->exactly( 2 ) )
            ->method( 'getStorage' )
            ->will(
                $this->returnValue( $storageMock )
            );

        $storageMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Field'
                )
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
                    'userId' => 14,
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
            ( $storageReg = new StorageRegistry() )
        );

        $stringStorageMock = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );
        $userStorageMock = $this->getMock(
            'ezp\\Persistence\\Fields\\Storage'
        );

        $storageReg->register( 'ezstring', $stringStorageMock );
        $storageReg->register( 'ezuser', $userStorageMock );

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

        $stringStorageMock->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with( $this->equalTo( array( 1, 2 ) ) );
        $userStorageMock->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with( $this->equalTo( array( 3 ) ) );

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
     * @covers ezp\Persistence\Storage\Legacy\Content\Handler::setStatus
     */
    public function testSetStatus()
    {
        $handler = new Handler(
            ( $gatewayMock = $this->getGatewayMock() ),
            $this->getLocationGatewayMock(),
            $this->getTypeGatewayMock(),
            ( $mapperMock = $this->getMapperMock() ),
            $this->getStorageRegistryMock()
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
