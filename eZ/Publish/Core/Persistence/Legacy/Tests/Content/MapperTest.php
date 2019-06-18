<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\MapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Content\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\API\Repository\Values\Content\Relation as RelationValue;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as LocationCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for Mapper.
 */
class MapperTest extends LanguageAwareTestCase
{
    /**
     * Value converter registry mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $valueConverterRegistryMock;

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::__construct
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
     * Returns a eZ\Publish\SPI\Persistence\Content\CreateStruct fixture.
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
        $struct->locations = [
            new LocationCreateStruct(
                ['parentId' => 2]
            ),
            new LocationCreateStruct(
                ['parentId' => 3]
            ),
            new LocationCreateStruct(
                ['parentId' => 4]
            ),
        ];
        $struct->fields = [new Field()];

        return $struct;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createVersionInfoForContent
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
            [
                'id' => null,
                'versionNo' => 1,
                'creatorId' => 14,
                'status' => 0,
                'initialLanguageCode' => 'eng-GB',
                'languageIds' => [4],
            ],
            $versionInfo
        );
        $this->assertGreaterThanOrEqual($time, $versionInfo->creationDate);
        $this->assertGreaterThanOrEqual($time, $versionInfo->modificationDate);
    }

    /**
     * Returns a Content fixture.
     *
     * @return Content
     */
    protected function getFullContentFixture()
    {
        $content = new Content();

        $content->fields = [
            new Field(['languageCode' => 'eng-GB']),
        ];
        $content->versionInfo = new VersionInfo(
            [
                'versionNo' => 1,
                'initialLanguageCode' => 'eng-GB',
                'languageIds' => [4],
            ]
        );

        $content->versionInfo->contentInfo = new ContentInfo();
        $content->versionInfo->contentInfo->id = 2342;
        $content->versionInfo->contentInfo->contentTypeId = 23;
        $content->versionInfo->contentInfo->sectionId = 42;
        $content->versionInfo->contentInfo->ownerId = 13;

        return $content;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::convertToStorageValue
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue
     */
    public function testConvertToStorageValue()
    {
        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects($this->once())
            ->method('toStorageValue')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\FieldValue'
                ),
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue'
                )
            )->will($this->returnValue(new StorageFieldValue()));

        $reg = new Registry(['some-type' => $convMock]);

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();

        $mapper = new Mapper($reg, $this->getLanguageHandler());
        $res = $mapper->convertToStorageValue($field);

        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue',
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentFromRows
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractFieldFromRow
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractFieldValueFromRow
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue
     */
    public function testExtractContentFromRows()
    {
        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects($this->exactly(13))
            ->method('toFieldValue')
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageFieldValue'
                )
            )->will(
                $this->returnValue(
                    new FieldValue()
                )
            );

        $reg = new Registry(
            [
                'ezauthor' => $convMock,
                'ezstring' => $convMock,
                'ezrichtext' => $convMock,
                'ezboolean' => $convMock,
                'ezimage' => $convMock,
                'ezdatetime' => $convMock,
                'ezkeyword' => $convMock,
                'ezsrrating' => $convMock,
            ]
        );

        $rowsFixture = $this->getContentExtractFixture();
        $nameRowsFixture = $this->getNamesExtractFixture();

        $mapper = new Mapper($reg, $this->getLanguageHandler());
        $result = $mapper->extractContentFromRows($rowsFixture, $nameRowsFixture);

        $this->assertEquals(
            [
                $this->getContentExtractReference(),
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentFromRows
     */
    public function testExtractContentFromRowsMultipleVersions()
    {
        $convMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter'
        );
        $convMock->expects($this->any())
            ->method('toFieldValue')
            ->will($this->returnValue(new FieldValue()));

        $reg = new Registry(
            [
                'ezstring' => $convMock,
                'ezrichtext' => $convMock,
                'ezdatetime' => $convMock,
            ]
        );

        $rowsFixture = $this->getMultipleVersionsExtractFixture();
        $nameRowsFixture = $this->getMultipleVersionsNamesExtractFixture();

        $mapper = new Mapper($reg, $this->getLanguageHandler());
        $result = $mapper->extractContentFromRows($rowsFixture, $nameRowsFixture);

        $this->assertEquals(
            2,
            count($result)
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     */
    public function testCreateCreateStructFromContent()
    {
        $time = time();
        $mapper = $this->getMapper();

        $content = $this->getContentExtractReference();

        $struct = $mapper->createCreateStructFromContent($content);

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\CreateStruct',
            $struct
        );

        return [
            'original' => $content,
            'result' => $struct,
            'time' => $time,
        ];

        // parentLocations
        // fields
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentBasicProperties($data)
    {
        $content = $data['original'];
        $struct = $data['result'];
        $time = $data['time'];
        $this->assertStructsEqual(
            $content->versionInfo->contentInfo,
            $struct,
            ['sectionId', 'ownerId']
        );
        self::assertNotEquals($content->versionInfo->contentInfo->remoteId, $struct->remoteId);
        self::assertSame($content->versionInfo->contentInfo->contentTypeId, $struct->typeId);
        self::assertSame(2, $struct->initialLanguageId);
        self::assertSame($content->versionInfo->contentInfo->alwaysAvailable, $struct->alwaysAvailable);
        self::assertGreaterThanOrEqual($time, $struct->modified);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentParentLocationsEmpty($data)
    {
        $this->assertEquals(
            [],
            $data['result']->locations
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentFieldCount($data)
    {
        $this->assertEquals(
            count($data['original']->fields),
            count($data['result']->fields)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     * @depends testCreateCreateStructFromContent
     */
    public function testCreateCreateStructFromContentFieldsNoId($data)
    {
        foreach ($data['result']->fields as $field) {
            $this->assertNull($field->id);
        }
    }

    public function testExtractRelationsFromRows()
    {
        $mapper = $this->getMapper();

        $rows = $this->getRelationExtractFixture();

        $res = $mapper->extractRelationsFromRows($rows);

        $this->assertEquals(
            $this->getRelationExtractReference(),
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createCreateStructFromContent
     */
    public function testCreateCreateStructFromContentWithPreserveOriginalLanguage()
    {
        $time = time();
        $mapper = $this->getMapper();

        $content = $this->getContentExtractReference();
        $content->versionInfo->contentInfo->mainLanguageCode = 'eng-GB';

        $struct = $mapper->createCreateStructFromContent($content, true);

        $this->assertInstanceOf(CreateStruct::class, $struct);
        $this->assertStructsEqual($content->versionInfo->contentInfo, $struct, ['sectionId', 'ownerId']);
        self::assertNotEquals($content->versionInfo->contentInfo->remoteId, $struct->remoteId);
        self::assertSame($content->versionInfo->contentInfo->contentTypeId, $struct->typeId);
        self::assertSame(2, $struct->initialLanguageId);
        self::assertSame(4, $struct->mainLanguageId);
        self::assertSame($content->versionInfo->contentInfo->alwaysAvailable, $struct->alwaysAvailable);
        self::assertGreaterThanOrEqual($time, $struct->modified);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::extractContentInfoFromRow
     * @dataProvider extractContentInfoFromRowProvider
     *
     * @param array $fixtures
     * @param string $prefix
     */
    public function testExtractContentInfoFromRow(array $fixtures, $prefix)
    {
        $contentInfoReference = $this->getContentExtractReference()->versionInfo->contentInfo;
        $mapper = new Mapper(
            $this->getValueConverterRegistryMock(),
            $this->getLanguageHandler()
        );
        self::assertEquals($contentInfoReference, $mapper->extractContentInfoFromRow($fixtures, $prefix));
    }

    /**
     * Returns test data for {@link testExtractContentInfoFromRow()}.
     *
     * @return array
     */
    public function extractContentInfoFromRowProvider()
    {
        $fixtures = $this->getContentExtractFixture();
        $fixturesNoPrefix = [];
        foreach ($fixtures[0] as $key => $value) {
            $keyNoPrefix = $key === 'ezcontentobject_tree_main_node_id'
                ? $key
                : str_replace('ezcontentobject_', '', $key);
            $fixturesNoPrefix[$keyNoPrefix] = $value;
        }

        return [
            [$fixtures[0], 'ezcontentobject_'],
            [$fixturesNoPrefix, ''],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Mapper::createRelationFromCreateStruct
     */
    public function testCreateRelationFromCreateStruct()
    {
        $struct = $this->getRelationCreateStructFixture();

        $mapper = $this->getMapper();
        $relation = $mapper->createRelationFromCreateStruct($struct);

        self::assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\Relation', $relation);
        foreach ($struct as $property => $value) {
            self::assertSame($value, $relation->$property);
        }
    }

    /**
     * Returns test data for {@link testExtractVersionInfoFromRow()}.
     *
     * @return array
     */
    public function extractVersionInfoFromRowProvider()
    {
        $fixturesAll = $this->getContentExtractFixture();
        $fixtures = $fixturesAll[0];
        $fixtures['ezcontentobject_version_names'] = [
            ['content_translation' => 'eng-US', 'name' => 'Something'],
        ];
        $fixtures['ezcontentobject_version_languages'] = [2];
        $fixtures['ezcontentobject_version_initial_language_code'] = 'eng-US';
        $fixturesNoPrefix = [];
        foreach ($fixtures as $key => $value) {
            $keyNoPrefix = str_replace('ezcontentobject_version_', '', $key);
            $fixturesNoPrefix[$keyNoPrefix] = $value;
        }

        return [
            [$fixtures, 'ezcontentobject_version_'],
            [$fixturesNoPrefix, ''],
        ];
    }

    /**
     * Returns a fixture of database rows for content extraction.
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
     * Returns a fixture of database rows for content names extraction.
     *
     * Fixture is stored in _fixtures/extract_names_from_rows.php
     *
     * @return array
     */
    protected function getNamesExtractFixture()
    {
        return require __DIR__ . '/_fixtures/extract_names_from_rows.php';
    }

    /**
     * Returns a reference result for content extraction.
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
     * Returns a fixture for mapping multiple versions of a content object.
     *
     * @return string[][]
     */
    protected function getMultipleVersionsExtractFixture()
    {
        return require __DIR__ . '/_fixtures/extract_content_from_rows_multiple_versions.php';
    }

    /**
     * Returns a fixture of database rows for content names extraction across multiple versions.
     *
     * Fixture is stored in _fixtures/extract_names_from_rows_multiple_versions.php
     *
     * @return array
     */
    protected function getMultipleVersionsNamesExtractFixture()
    {
        return require __DIR__ . '/_fixtures/extract_names_from_rows_multiple_versions.php';
    }

    /**
     * Returns a fixture of database rows for relations extraction.
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
     * Returns a reference result for content extraction.
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
     * Returns a Mapper.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getMapper($valueConverter = null)
    {
        return new Mapper(
            $this->getValueConverterRegistryMock(),
            $this->getLanguageHandler()
        );
    }

    /**
     * Returns a FieldValue converter registry mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected function getValueConverterRegistryMock()
    {
        if (!isset($this->valueConverterRegistryMock)) {
            $this->valueConverterRegistryMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\ConverterRegistry',
                [],
                [[]]
            );
        }

        return $this->valueConverterRegistryMock;
    }

    /**
     * Returns a eZ\Publish\SPI\Persistence\Content\CreateStruct fixture.
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
     * Returns a language handler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected function getLanguageHandler()
    {
        $languages = [
            new Language(
                [
                    'id' => 2,
                    'languageCode' => 'eng-US',
                    'name' => 'US english',
                ]
            ),
            new Language(
                [
                    'id' => 4,
                    'languageCode' => 'eng-GB',
                    'name' => 'British english',
                ]
            ),
        ];

        if (!isset($this->languageHandler)) {
            $this->languageHandler = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler');
            $this->languageHandler->expects($this->any())
                ->method('load')
                ->will(
                    $this->returnCallback(
                        function ($id) use ($languages) {
                            foreach ($languages as $language) {
                                if ($language->id == $id) {
                                    return $language;
                                }
                            }
                        }
                    )
                );
            $this->languageHandler->expects($this->any())
                ->method('loadByLanguageCode')
                ->will(
                    $this->returnCallback(
                        function ($languageCode) use ($languages) {
                            foreach ($languages as $language) {
                                if ($language->languageCode == $languageCode) {
                                    return $language;
                                }
                            }
                        }
                    )
                );
        }

        return $this->languageHandler;
    }
}
