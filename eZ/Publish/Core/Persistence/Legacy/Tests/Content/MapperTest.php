<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\MapperTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\RestrictedVersion,
    eZ\Publish\SPI\Persistence\Content\Language,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as LocationCreateStruct;

/**
 * Test case for Mapper
 */
class MapperTest extends TestCase
{
    /**
     * Location mapper mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapperMock;

    /**
     * Value converter registry mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $valueConverterRegistryMock;

    /**
     * Language handler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::__construct
     */
    public function testCtor()
    {
        $regMock = $this->getValueConverterRegistryMock();

        $mapper = $this->getMapper();

        $this->assertAttributeSame(
            $regMock,
            'converterRegistry',
            $mapper
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createContentFromCreateStruct
     */
    public function testCreateContentFromCreateStruct()
    {
        $struct = $this->getCreateStructFixture();

        $mapper = $this->getMapper();
        $this->languageHandler
            ->expects( $this->once() )
            ->method( 'getById' )
            ->with( '2' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 2,
                            'languageCode' => 'eng-GB',
                        )
                    )
                )
            );
        $content = $mapper->createContentFromCreateStruct( $struct );

        self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' , $content );
        self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo', $content->contentInfo );
        $this->assertStructsEqual(
            $struct,
            $content->contentInfo,
            array( 'sectionId', 'ownerId', 'remoteId' )
        );
        self::assertSame( $struct->typeId, $content->contentInfo->contentTypeId );
        self::assertSame( 'eng-GB', $content->contentInfo->mainLanguageCode );
        self::assertSame( $struct->alwaysAvailable, $content->contentInfo->isAlwaysAvailable );
        self::assertSame( 0, $content->contentInfo->publicationDate );
        self::assertSame( 0, $content->contentInfo->modificationDate );
        self::assertEquals( 1, $content->contentInfo->currentVersionNo );
        self::assertFalse( $content->contentInfo->isPublished );
    }

    /**
     * Returns a eZ\Publish\SPI\Persistence\Content\CreateStruct fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $struct->name = 'Content name';
        $struct->typeId = 23;
        $struct->sectionId = 42;
        $struct->ownerId = 13;
        $struct->initialLanguageId = 2;
        $struct->locations = array(
            new LocationCreateStruct(
                array( 'parentId' => 2 )
            ),
            new LocationCreateStruct(
                array( 'parentId' => 3 )
            ),
            new LocationCreateStruct(
                array( 'parentId' => 4 )
            )
        );
        $struct->fields = array( new Field(), );

        return $struct;
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createVersionInfoForContent
     */
    public function testCreateVersionFromContent()
    {
        $content = $this->getFullContentFixture();

        $mapper = $this->getMapper();
        $this->languageHandler
            ->expects( $this->once() )
            ->method( 'loadByLanguageCode' )
            ->with( 'eng-GB' )
            ->will(
            $this->returnValue(
                new Language(
                    array(
                        'id' => 2,
                        'languageCode' => 'eng-GB',
                    )
                )
            )
        );
        $time = time();
        $versionInfo = $mapper->createVersionInfoForContent(
            $content,
            1,
            $content->fields,
            $content->versionInfo->initialLanguageCode
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'versionNo' => 1,
                'creatorId' => 13,
                'status' => 0,
                'contentId' => 2342,
                'initialLanguageCode' => 'eng-GB',
                'languageIds' => array( 2 ),

            ),
            $versionInfo
        );

        $this->assertGreaterThanOrEqual( $time, $versionInfo->modificationDate );
        $this->assertGreaterThanOrEqual( $time, $versionInfo->creationDate );
    }

    /**
     * Returns a Content fixture
     *
     * @return Content
     */
    protected function getContentFixture()
    {
        $content = new Content;
        $content->contentInfo = new ContentInfo;
        $content->contentInfo->contentId = 2342;
        $content->contentInfo->contentTypeId = 23;
        $content->contentInfo->sectionId = 42;
        $content->contentInfo->ownerId = 13;
        $content->fields = array(
            new Field( array( "languageCode" => "eng-GB" ) ),
        );
        $content->locations = array();

        return $content;
    }

    protected function getFullContentFixture()
    {
        $content = $this->getContentFixture();

        $content->versionInfo = new VersionInfo(
            array(
                'versionNo' => 1,
                'initialLanguageCode' => 'eng-GB'
            )
        );

        return $content;
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::convertToStorageValue
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue
     */
    public function testConvertToStorageValue()
    {
        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->once() )
            ->method( 'toStorageValue' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\FieldValue'
                ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue'
                )
            )->will( $this->returnValue( new StorageFieldValue() ) );

        $reg = new Registry();
        $reg->register( 'some-type', $convMock );

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();

        $mapper = new Mapper( $this->getLocationMapperMock(), $reg, $this->getLanguageHandlerMock() );
        $res = $mapper->convertToStorageValue( $field );

        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue',
            $res
        );
    }

    /**
     * @return void
     * @todo Load referencing locations!
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentFromRows
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentFromRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractVersionFromRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::mapCommonVersionFields
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractFieldFromRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractFieldValueFromRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue
     */
    public function testExtractContentFromRows()
    {
        $locationMapperMock = $this->getLocationMapperMock();
        $locationMapperMock->expects( $this->once() )
            ->method( 'createLocationFromRow' )
            ->with( $this->isType( 'array' ) )
            ->will( $this->returnValue( new Content\Location() ) );

        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->exactly( 12 ) )
            ->method( 'toFieldValue' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue'
                )
            )->will(
                $this->returnValue(
                    new FieldValue()
                )
            );

        $reg = new Registry();
        $reg->register( 'ezauthor', $convMock );
        $reg->register( 'ezstring', $convMock );
        $reg->register( 'ezxmltext', $convMock );
        $reg->register( 'ezboolean', $convMock );
        $reg->register( 'ezimage', $convMock );
        $reg->register( 'ezdatetime', $convMock );
        $reg->register( 'ezkeyword', $convMock );
        $reg->register( 'ezsrrating', $convMock );

        $rowsFixture = $this->getContentExtractFixture();

        $mapper = new Mapper( $locationMapperMock, $reg, $this->getLanguageHandlerMock() );
        $result = $mapper->extractContentFromRows( $rowsFixture );

        $this->assertEquals(
            array(
                $this->getContentExtractReference(),
            ),
            $result
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentFromRows
     */
    public function testExtractContentFromRowsMultipleVersions()
    {
        $locationMapperMock = $this->getLocationMapperMock();
        $locationMapperMock->expects( $this->any() )
            ->method( 'createLocationFromRow' )
            ->will( $this->returnValue( new Content\Location() ) );

        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->any() )
            ->method( 'toFieldValue' )
            ->will( $this->returnValue( new FieldValue() ) );

        $reg = new Registry();
        $reg->register( 'ezstring', $convMock );
        $reg->register( 'ezxmltext', $convMock );
        $reg->register( 'ezdatetime', $convMock );

        $rowsFixture = $this->getMultipleVersionsExtractFixture();

        $mapper = new Mapper( $locationMapperMock, $reg, $this->getLanguageHandlerMock() );
        $result = $mapper->extractContentFromRows( $rowsFixture );

        $this->assertEquals(
            2,
            count( $result )
        );

        $this->assertEquals(
            11,
            $result[0]->contentInfo->contentId
        );
        $this->assertEquals(
            11,
            $result[1]->contentInfo->contentId
        );

        $this->assertEquals(
            1,
            $result[0]->versionInfo->versionNo
        );
        $this->assertEquals(
            2,
            $result[1]->versionInfo->versionNo
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractVersionListFromRows
     */
    public function testExtractVersionListFromRows()
    {
        $mapper = $this->getMapper();

        $rows = $this->getRestrictedVersionExtractFixture();

        $res = $mapper->extractVersionListFromRows( $rows );

        $this->assertEquals(
            $this->getRestrictedVersionExtractReference(),
            $res
        );
    }

    /**
     * @return CreateStruct
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     */
    public function testCreateCreateStructFromContent()
    {
        $mapper = $this->getMapper();
        $this->languageHandler
            ->expects( $this->once() )
            ->method( 'getByLocale' )
            ->with( 'eng-US' )
            ->will(
                $this->returnValue(
                    new Language(
                        array(
                            'id' => 2,
                            'languageCode' => 'eng-US',
                        )
                    )
                )
            );

        $content = $this->getContentExtractReference();

        $struct = $mapper->createCreateStructFromContent( $content );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\CreateStruct',
            $struct
        );
        return array(
            'original' => $content,
            'result' => $struct
        );

        // parentLocations
        // fields
    }

    /**
     * @return CreateStruct
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentBasicProperties( $data )
    {
        $content = $data['original'];
        $struct = $data['result'];
        $this->assertStructsEqual(
            $content->contentInfo,
            $struct,
            array( 'sectionId', 'ownerId', 'remoteId' )
        );
        self::assertSame( $content->contentInfo->contentTypeId, $struct->typeId );
        self::assertSame( 2, $struct->initialLanguageId );
        self::assertSame( $content->contentInfo->isAlwaysAvailable, $struct->alwaysAvailable );
        self::assertSame( $content->contentInfo->publicationDate, $struct->published );
        self::assertSame( $content->contentInfo->modificationDate, $struct->modified );

    }

    /**
     * @return CreateStruct
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentParentLocationsEmpty( $data )
    {
        $this->assertEquals(
            array(),
            $data['result']->locations
        );
    }

    /**
     * @return CreateStruct
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentFieldCount( $data )
    {
        $this->assertEquals(
            count( $data['original']->fields ),
            count( $data['result']->fields )
        );
    }

    /**
     * @return CreateStruct
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentFieldsNoId( $data )
    {
        foreach ( $data['result']->fields as $field )
        {
            $this->assertNull( $field->id );
        }
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentInfoFromRow
     * @dataProvider extractContentInfoFromRowProvider
     * @param array $fixtures
     * @param string $prefix
     */
    public function testExtractContentInfoFromRow( array $fixtures, $prefix )
    {
        $contentInfoReference = $this->getContentExtractReference()->contentInfo;
        $mapper = new Mapper(
            $this->getLocationMapperMock(),
            $this->getValueConverterRegistryMock(),
            $this->getLanguageHandlerMock()
        );
        self::assertEquals( $contentInfoReference, $mapper->extractContentInfoFromRow( $fixtures, $prefix) );
    }

    /**
     * Returns test data for {@link testExtractContentInfoFromRow()}
     *
     * @return array
     */
    public function extractContentInfoFromRowProvider()
    {
        $fixtures = $this->getContentExtractFixture();
        $fixturesNoPrefix = array();
        foreach ( $fixtures[0] as $key => $value )
        {
            $keyNoPrefix = str_replace( 'ezcontentobject_', '', $key );
            $fixturesNoPrefix[$keyNoPrefix] = $value;
        }

        return array(
            array( $fixtures[0], 'ezcontentobject_' ),
            array( $fixturesNoPrefix, '' )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractVersionInfoFromRow
     * @dataProvider extractVersionInfoFromRowProvider
     * @param array $fixtures
     * @param string $prefix
     */
    public function testExtractVersionInfoFromRow( array $fixtures, $prefix )
    {
        $versionInfoReference = $this->getContentExtractReference()->versionInfo;
        $mapper = new Mapper(
            $this->getLocationMapperMock(),
            $this->getValueConverterRegistryMock(),
            $this->getLanguageHandlerMock()
        );

        $versionInfo = $mapper->extractVersionInfoFromRow( $fixtures, $prefix );
        foreach ( $versionInfoReference as $property => $value )
        {
            switch ( $property )
            {
                default:
                    self::assertSame( $value, $versionInfo->$property );
            }
        }
    }

    /**
     * Returns test data for {@link testExtractVersionInfoFromRow()}
     *
     * @return array
     */
    public function extractVersionInfoFromRowProvider()
    {
        $fixturesAll = $this->getContentExtractFixture();
        $fixtures = $fixturesAll[0];
        $fixtures['ezcontentobject_version_names'] = array(
            array( 'content_translation' => 'eng-US', 'name' => 'Something' )
        );
        $fixtures['ezcontentobject_version_languages'] = array( 2 );
        $fixtures['ezcontentobject_version_initial_language_code'] = 'eng-US';
        $fixturesNoPrefix = array();
        foreach ( $fixtures as $key => $value )
        {
            $keyNoPrefix = str_replace( 'ezcontentobject_version_', '', $key );
            $fixturesNoPrefix[$keyNoPrefix] = $value;
        }

        return array(
            array( $fixtures, 'ezcontentobject_version_' ),
            array( $fixturesNoPrefix, '' )
        );
    }


    /**
     * Returns a fixture of database rows for content extraction
     *
     * Fixture is stored in _fixtures/extract_content_from_rows.php
     *
     * @return array
     */
    protected function getContentExtractFixture()
    {
        return require __DIR__ . '/_fixtures/extract_content_from_rows.php';
    }

    /**
     * Returns a reference result for content extraction
     *
     * Fixture is stored in _fixtures/extract_content_from_rows_result.php
     *
     * @return Content
     */
    protected function getContentExtractReference()
    {
        return require __DIR__ . '/_fixtures/extract_content_from_rows_result.php';
    }

    /**
     * Returns a fixture for mapping RestrictedVersion objects
     *
     * @return string[][]
     */
    protected function getRestrictedVersionExtractFixture()
    {
        return require __DIR__ . '/_fixtures/restricted_version_rows.php';
    }

    /**
     * Returns a fixture for mapping multiple versions of a content object
     *
     * @return string[][]
     */
    protected function getMultipleVersionsExtractFixture()
    {
        return require __DIR__ . '/_fixtures/extract_content_from_rows_multiple_versions.php';
    }

    /**
     * Returns a reference result for mapping RestrictedVersion objects
     *
     * @return RestrictedVersion[]
     */
    protected function getRestrictedVersionExtractReference()
    {
        $versions = array();

        $version = new RestrictedVersion();
        $version->id = 675;
        $version->name = array( "eng-US" => "Something" );
        $version->versionNo = 1;
        $version->modified = 1313047907;
        $version->creatorId = 14;
        $version->created = 1313047865;
        $version->status = 3;
        $version->contentId = 226;
        $version->initialLanguageId = 2;
        $version->languageIds = array( 2 );

        $versions[] = $version;

        $version = new RestrictedVersion();
        $version->id = 676;
        $version->name = array( "eng-US" => "Something" );
        $version->versionNo = 2;
        $version->modified = 1313061404;
        $version->creatorId = 14;
        $version->created = 1313061317;
        $version->status = 1;
        $version->contentId = 226;
        $version->initialLanguageId = 2;
        $version->languageIds = array( 2 );

        $versions[] = $version;

        return $versions;
    }

    /**
     * Returns a Mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getMapper( $locationMapper = null, $valueConverter = null )
    {
        return new Mapper(
            $this->getLocationMapperMock(),
            $this->getValueConverterRegistryMock(),
            $this->getLanguageHandlerMock()
        );
    }

    /**
     * Returns a location mapper mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapperMock()
    {
        if ( !isset( $this->locationMapperMock ) )
        {
            $this->locationMapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->locationMapperMock;
    }

    /**
     * Returns a FieldValue converter registry mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Registry
     */
    protected function getValueConverterRegistryMock()
    {
        if ( !isset( $this->valueConverterRegistryMock ) )
        {
            $this->valueConverterRegistryMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Registry'
            );
        }
        return $this->valueConverterRegistryMock;
    }

    /**
     * Returns a language handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected function getLanguageHandlerMock()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $innerLanguageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
            $innerLanguageHandler->expects( $this->any() )
                ->method( 'loadAll' )
                ->will(
                    $this->returnValue(
                        array(
                            new Language( array(
                                'id'            => 2,
                                'languageCode'  => 'eng-GB',
                                'name'          => 'British english'
                            ) ),
                            new Language( array(
                                'id'            => 4,
                                'languageCode'  => 'eng-US',
                                'name'          => 'US english'
                            ) ),
                            new Language( array(
                                'id'            => 8,
                                'languageCode'  => 'fre-FR',
                                'name'          => 'Français franchouillard'
                            ) )
                        )
                    )
                );
            $this->languageHandler = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\CachingHandler',
                array( 'getByLocale', 'getById', 'loadByLanguageCode' ),
                array(
                    $innerLanguageHandler,
                    $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Cache' )
                )
            );
        }
        return $this->languageHandler;
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
