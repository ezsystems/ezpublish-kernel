<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\MapperTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\API\Repository\Values\Content\Relation as RelationValue,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Language,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for Mapper
 */
class MapperTest extends LanguageAwareTestCase
{
    /**
     * Value converter registry mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $valueConverterRegistryMock;

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
    public function testCreateVersionInfoForContent()
    {
        $content = $this->getFullContentFixture();
        $time = time();

        $mapper = $this->getMapper();

        $versionInfo = $mapper->createVersionInfoForContent(
            $content,
            1,
            14
        );

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'versionNo' => 1,
                'creatorId' => 14,
                'status' => 0,
                'initialLanguageCode' => 'eng-GB',
                'languageIds' => array( 4 ),
            ),
            $versionInfo
        );
        $this->assertGreaterThanOrEqual( $time, $versionInfo->creationDate );
        $this->assertGreaterThanOrEqual( $time, $versionInfo->modificationDate );
    }

    /**
     * Returns a Content fixture
     *
     * @return Content
     */
    protected function getFullContentFixture()
    {
        $content = new Content;

        $content->fields = array(
            new Field( array( "languageCode" => "eng-GB" ) ),
        );
        $content->versionInfo = new VersionInfo(
            array(
                'versionNo' => 1,
                'initialLanguageCode' => 'eng-GB',
                'languageIds' => array( 4 ),
            )
        );

        $content->versionInfo->contentInfo = new ContentInfo;
        $content->versionInfo->contentInfo->id = 2342;
        $content->versionInfo->contentInfo->contentTypeId = 23;
        $content->versionInfo->contentInfo->sectionId = 42;
        $content->versionInfo->contentInfo->ownerId = 13;

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

        $reg = new Registry( array( 'some-type' => $convMock ) );

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();

        $mapper = new Mapper( $reg, $this->getLanguageHandler() );
        $res = $mapper->convertToStorageValue( $field );

        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue',
            $res
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentFromRows
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractFieldFromRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractFieldValueFromRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue
     */
    public function testExtractContentFromRows()
    {
        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->exactly( 13 ) )
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

        $reg = new Registry( array(
            'ezauthor' => $convMock,
            'ezstring' => $convMock,
            'ezxmltext' => $convMock,
            'ezboolean' => $convMock,
            'ezimage' => $convMock,
            'ezdatetime' => $convMock,
            'ezkeyword' => $convMock,
            'ezsrrating' => $convMock
         ) );

        $rowsFixture = $this->getContentExtractFixture();

        $mapper = new Mapper( $reg, $this->getLanguageHandler() );
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
        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->any() )
            ->method( 'toFieldValue' )
            ->will( $this->returnValue( new FieldValue() ) );

        $reg = new Registry( array(
            'ezstring' => $convMock,
            'ezxmltext'=>  $convMock,
            'ezdatetime' => $convMock,
         ) );

        $rowsFixture = $this->getMultipleVersionsExtractFixture();

        $mapper = new Mapper( $reg, $this->getLanguageHandler() );
        $result = $mapper->extractContentFromRows( $rowsFixture );

        $this->assertEquals(
            2,
            count( $result )
        );

        $this->assertEquals(
            11,
            $result[0]->versionInfo->contentInfo->id
        );
        $this->assertEquals(
            11,
            $result[1]->versionInfo->contentInfo->id
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     */
    public function testCreateCreateStructFromContent()
    {
        $time = time();
        $mapper = $this->getMapper();

        $content = $this->getContentExtractReference();

        $struct = $mapper->createCreateStructFromContent( $content );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\CreateStruct',
            $struct
        );
        return array(
            'original' => $content,
            'result' => $struct,
            'time' => $time
        );

        // parentLocations
        // fields
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentBasicProperties( $data )
    {
        $content = $data['original'];
        $struct = $data['result'];
        $time = $data['time'];
        $this->assertStructsEqual(
            $content->versionInfo->contentInfo,
            $struct,
            array( 'sectionId', 'ownerId' )
        );
        self::assertNotEquals( $content->versionInfo->contentInfo->remoteId, $struct->remoteId );
        self::assertSame( $content->versionInfo->contentInfo->contentTypeId, $struct->typeId );
        self::assertSame( 2, $struct->initialLanguageId );
        self::assertSame( $content->versionInfo->contentInfo->alwaysAvailable, $struct->alwaysAvailable );
        self::assertGreaterThanOrEqual( $time, $struct->modified );
    }

    /**
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

    public function testExtractRelationsFromRows()
    {
        $mapper = $this->getMapper();

        $rows = $this->getRelationExtractFixture();

        $res = $mapper->extractRelationsFromRows( $rows );

        $this->assertEquals(
            $this->getRelationExtractReference(),
            $res
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentInfoFromRow
     * @dataProvider extractContentInfoFromRowProvider
     * @param array $fixtures
     * @param string $prefix
     */
    public function testExtractContentInfoFromRow( array $fixtures, $prefix )
    {
        $contentInfoReference = $this->getContentExtractReference()->versionInfo->contentInfo;
        $mapper = new Mapper(
            $this->getValueConverterRegistryMock(),
            $this->getLanguageHandler()
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
            $keyNoPrefix = $key === "ezcontentobject_tree_main_node_id"
                ? $key
                : str_replace( 'ezcontentobject_', '', $key );
            $fixturesNoPrefix[$keyNoPrefix] = $value;
        }

        return array(
            array( $fixtures[0], 'ezcontentobject_' ),
            array( $fixturesNoPrefix, '' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createContentFromCreateStruct
     */
    public function testCreateRelationFromCreateStruct()
    {
        $struct = $this->getRelationCreateStructFixture();

        $mapper = $this->getMapper();
        $relation = $mapper->createRelationFromCreateStruct( $struct );

        self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Relation' , $relation );
        foreach( $struct as $property => $value )
        {
            self::assertSame( $value, $relation->$property );
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
     * Returns a fixture for mapping multiple versions of a content object
     *
     * @return string[][]
     */
    protected function getMultipleVersionsExtractFixture()
    {
        return require __DIR__ . '/_fixtures/extract_content_from_rows_multiple_versions.php';
    }

    /**
     * Returns a fixture of database rows for relations extraction
     *
     * Fixture is stored in _fixtures/relations.php
     *
     * @return array
     */
    protected function getRelationExtractFixture()
    {
        return require __DIR__ . '/_fixtures/relations_rows.php';
    }

    /**
     * Returns a reference result for content extraction
     *
     * Fixture is stored in _fixtures/relations_results.php
     *
     * @return Content
     */
    protected function getRelationExtractReference()
    {
        return require __DIR__ . '/_fixtures/relations_results.php';
    }

    /**
     * Returns a Mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getMapper( $valueConverter = null )
    {
        return new Mapper(
            $this->getValueConverterRegistryMock(),
            $this->getLanguageHandler()
        );
    }

    /**
     * Returns a FieldValue converter registry mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected function getValueConverterRegistryMock()
    {
        if ( !isset( $this->valueConverterRegistryMock ) )
        {
            $this->valueConverterRegistryMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\ConverterRegistry',
                array(),
                array( array() )
            );
        }
        return $this->valueConverterRegistryMock;
    }

    /**
     * Returns a eZ\Publish\SPI\Persistence\Content\CreateStruct fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct
     */
    protected function getRelationCreateStructFixture()
    {
        $struct = new RelationCreateStruct();

        $struct->destinationContentId = 0;
        $struct->sourceContentId = 0;
        $struct->sourceContentVersionNo = 1;
        $struct->sourceFieldDefinitionId = 1;
        $struct->type = RelationValue::COMMON;

        return $struct;
    }

    /**
     * Returns a language handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected function getLanguageHandler()
    {
        $languages = array(
            new Language(
                array(
                    'id' => 2,
                    'languageCode' => 'eng-US',
                    'name' => 'US english'
                )
            ),
            new Language(
                array(
                    'id' => 4,
                    'languageCode' => 'eng-GB',
                    'name' => 'British english'
                )
            )
        );

        if ( !isset( $this->languageHandler ) )
        {
            $this->languageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
            $this->languageHandler->expects( $this->any() )
                ->method( 'load' )
                ->will(
                    $this->returnCallback(
                        function ( $id ) use ( $languages )
                        {
                            foreach ( $languages as $language )
                            {
                                if ( $language->id == $id )
                                {
                                    return $language;
                                }
                            }
                        }
                    )
                );
            $this->languageHandler->expects( $this->any() )
                ->method( 'loadByLanguageCode' )
                ->will(
                    $this->returnCallback(
                        function ( $languageCode ) use ( $languages )
                        {
                            foreach ( $languages as $language )
                            {
                                if ( $language->languageCode == $languageCode )
                                {
                                    return $language;
                                }
                            }
                        }
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
