<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\MapperTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Mapper,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\RestrictedVersion,
    ezp\Persistence\Content\CreateStruct;

/**
 * Test case for Mapper
 */
class MapperTest extends TestCase
{
    /**
     * Location mapper mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Mapper
     */
    protected $locationMapperMock;

    /**
     * Value converter registry mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $valueConverterRegistryMock;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Mapper::__construct
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
     * @covers ezp\Persistence\Storage\Legacy\Content\Mapper::createContentFromCreateStruct
     */
    public function testCreateContentFromCreateStruct()
    {
        $struct = $this->getCreateStructFixture();

        $mapper = $this->getMapper();
        $content = $mapper->createContentFromCreateStruct( $struct );

        $this->assertStructsEqual(
            $struct,
            $content,
            array( 'name', 'typeId', 'sectionId', 'ownerId' )
        );
    }

    /**
     * Returns a ezp\Persistence\Content\CreateStruct fixture
     *
     * @return \ezp\Persistence\Content\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $struct->name = 'Content name';
        $struct->typeId = 23;
        $struct->sectionId = 42;
        $struct->ownerId = 13;
        $struct->parentLocations = array( 2, 3, 4, );
        $struct->fields = array( new Field(), );

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Mapper::createVersionForContent
     */
    public function testCreateVersionFromContent()
    {
        $content = $this->getContentFixture();

        $mapper = $this->getMapper();
        $version = $mapper->createVersionForContent( $content, 1 );

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'versionNo' => 1,
                'creatorId' => 13,
                'state' => 0,
                'contentId' => 2342,
                'fields' => array(),
            ),
            $version
        );

        $this->assertAttributeGreaterThanOrEqual(
            time() - 1000,
            'created',
            $version
        );
        $this->assertAttributeGreaterThanOrEqual(
            time() - 1000,
            'modified',
            $version
        );
    }

    public function testCreateLocationFromContent()
    {
        $mapper = $this->getMapper();
        $location = $mapper->createLocationCreateStruct(
            $content = $this->getFullContentFixture(),
            $struct = $this->getCreateStructFixture()
        );

        $this->assertPropertiesCorrect(
            array(
                'contentId' => $content->id,
                'contentVersion' => 1,
            ),
            $location
        );
    }

    /**
     * Returns a Content fixture
     *
     * @return Content
     */
    protected function getContentFixture()
    {
        $struct = new Content();

        $struct->id = 2342;
        $struct->name = 'Content name';
        $struct->typeId = 23;
        $struct->sectionId = 42;
        $struct->ownerId = 13;
        $struct->locations = array();

        return $struct;
    }

    protected function getFullContentFixture()
    {
        $struct = $this->getContentFixture();

        $struct->version = new Content\Version(
            array(
                'id' => 1,
            )
        );

        return $struct;
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Mapper::convertToStorageValue
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageFieldValue
     */
    public function testConvertToStorageValue()
    {
        $convMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->once() )
            ->method( 'toStorageValue' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\FieldValue'
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue'
                )
            )->will( $this->returnValue( new StorageFieldValue() ) );

        $reg = new Registry();
        $reg->register( 'some-type', $convMock );

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();

        $mapper = new Mapper( $this->getLocationMapperMock(), $reg );
        $res = $mapper->convertToStorageValue( $field );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue',
            $res
        );
    }

    /**
     * @return void
     * @todo Load referencing locations!
     */
    public function testExtractContentFromRows()
    {
        $locationMapperMock = $this->getLocationMapperMock();
        $locationMapperMock->expects( $this->once() )
            ->method( 'createLocationFromRow' )
            ->with( $this->isType( 'array' ) )
            ->will( $this->returnValue( new Content\Location() ) );

        $convMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->exactly( 6 ) )
            ->method( 'toFieldValue' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageFieldValue'
                )
            )->will( $this->returnValue( new FieldValue() ) );

        $reg = new Registry();
        $reg->register( 'ezstring', $convMock );
        $reg->register( 'ezxmltext', $convMock );
        $reg->register( 'ezdatetime', $convMock );

        $rowsFixture = $this->getContentExtractFixture();

        $mapper = new Mapper( $locationMapperMock, $reg );
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
     * @covers ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase::extractContentFromRows
     */
    public function testExtractContentFromRowsMultipleVersions()
    {
        $locationMapperMock = $this->getLocationMapperMock();
        $locationMapperMock->expects( $this->any() )
            ->method( 'createLocationFromRow' )
            ->will( $this->returnValue( new Content\Location() ) );

        $convMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects( $this->any() )
            ->method( 'toFieldValue' )
            ->will( $this->returnValue( new FieldValue() ) );

        $reg = new Registry();
        $reg->register( 'ezstring', $convMock );
        $reg->register( 'ezxmltext', $convMock );
        $reg->register( 'ezdatetime', $convMock );

        $rowsFixture = $this->getMultipleVersionsExtractFixture();

        $mapper = new Mapper( $locationMapperMock, $reg );
        $result = $mapper->extractContentFromRows( $rowsFixture );

        $this->assertEquals(
            2,
            count( $result )
        );

        $this->assertEquals(
            11,
            $result[0]->id
        );
        $this->assertEquals(
            11,
            $result[1]->id
        );

        $this->assertEquals(
            1,
            $result[0]->version->versionNo
        );
        $this->assertEquals(
            2,
            $result[1]->version->versionNo
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Mapper::extractVersionListFromRows
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
        $version->versionNo = 1;
        $version->modified = 1313047907;
        $version->creatorId = 14;
        $version->created = 1313047865;
        $version->state = 3;
        $version->contentId = 226;
        $version->languageIds = array( 'eng-US' );

        $versions[] = $version;

        $version = new RestrictedVersion();
        $version->id = 676;
        $version->versionNo = 2;
        $version->modified = 1313061404;
        $version->creatorId = 14;
        $version->created = 1313061317;
        $version->state = 1;
        $version->contentId = 226;
        $version->languageIds = array( 'eng-US' );

        $versions[] = $version;

        return $versions;
    }

    /**
     * Returns a Mapper
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected function getMapper( $locationMapper = null, $valueConverter = null )
    {
        return new Mapper(
            $this->getLocationMapperMock(),
            $this->getValueConverterRegistryMock()
        );
    }

    /**
     * Returns a location mapper mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Location\Mapper
     */
    protected function getLocationMapperMock()
    {
        if ( !isset( $this->locationMapperMock ) )
        {
            $this->locationMapperMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Mapper',
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
     * @return \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected function getValueConverterRegistryMock()
    {
        if ( !isset( $this->valueConverterRegistryMock ) )
        {
            $this->valueConverterRegistryMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry'
            );
        }
        return $this->valueConverterRegistryMock;
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
