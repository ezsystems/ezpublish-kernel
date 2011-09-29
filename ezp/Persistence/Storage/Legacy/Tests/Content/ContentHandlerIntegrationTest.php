<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\ContentHandlerIntegrationTest class
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
 * Integration test case for Content Handler
 */
class ContentHandlerIntegrationTest extends TestCase
{
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
            $storageHandlerMock = $this->getStorageHandlerMock()
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

        $storageHandlerMock
            ->expects( $this->any() )
            ->method( 'getFieldData' );

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
            $storageHandlerMock = $this->getStorageHandlerMock()
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

        $storageHandlerMock
            ->expects( $this->exactly( 5 ) )
            ->method( 'getFieldData' )
            ->with( $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ) );

        $content = $handler->load( 14, 4 );
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
        $storageHandlerMock = $this->getStorageHandlerMock();

        $handler = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            array( 'load' ),
            array( $gatewayMock, $locationMock, $typeGatewayMock, $mapper, $storageHandlerMock )
        );

        // Handler expects load() to be called on itself, where we return a "proper"
        // content object.
        $handler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( 14, 4 )
            ->will( $this->returnValue( $content ) );

        // Ensure the external storage handler is called properly.
        $storageHandlerMock->expects( $this->exactly( 5 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' )
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
            $this->getStorageHandlerMock()
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
