<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Type\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\Relation as RelationValue;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends LanguageAwareTestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase
     */
    protected $databaseGateway;

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::__construct
     */
    public function testCtor()
    {
        $handlerMock = $this->getDatabaseHandler();
        $gateway = $this->getDatabaseGateway();

        $this->assertAttributeSame(
            $handlerMock,
            'dbHandler',
            $gateway
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::insertContentObject
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::generateLanguageMask
     *
     * @todo Fix not available fields
     */
    public function testInsertContentObject()
    {
        $struct = $this->getCreateStructFixture();

        $gateway = $this->getDatabaseGateway();
        $gateway->insertContentObject($struct);

        $this->assertQueryResult(
            [
                [
                    'name' => 'Content name',
                    'contentclass_id' => '23',
                    'section_id' => '42',
                    'owner_id' => '13',
                    'current_version' => '1',
                    'initial_language_id' => '2',
                    'remote_id' => 'some_remote_id',
                    'language_mask' => '3',
                    'modified' => '0',
                    'published' => '0',
                    'status' => ContentInfo::STATUS_DRAFT,
                ],
            ],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    [
                        'name',
                        'contentclass_id',
                        'section_id',
                        'owner_id',
                        'current_version',
                        'initial_language_id',
                        'remote_id',
                        'language_mask',
                        'modified',
                        'published',
                        'status',
                    ]
                )->from('ezcontentobject')
        );
    }

    /**
     * Returns a Content fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $struct->typeId = 23;
        $struct->sectionId = 42;
        $struct->ownerId = 13;
        $struct->initialLanguageId = 2;
        $struct->remoteId = 'some_remote_id';
        $struct->alwaysAvailable = true;
        $struct->modified = 456;
        $struct->name = [
            'eng-US' => 'Content name',
        ];
        $struct->fields = [
            new Field(['languageCode' => 'eng-US']),
        ];
        $struct->locations = [];

        return $struct;
    }

    /**
     * Returns a Content fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = new Content();

        $content->versionInfo = new VersionInfo();
        $content->versionInfo->names = [
            'eng-US' => 'Content name',
        ];
        $content->versionInfo->status = VersionInfo::STATUS_PENDING;

        $content->versionInfo->contentInfo = new ContentInfo();
        $content->versionInfo->contentInfo->contentTypeId = 23;
        $content->versionInfo->contentInfo->sectionId = 42;
        $content->versionInfo->contentInfo->ownerId = 13;
        $content->versionInfo->contentInfo->currentVersionNo = 2;
        $content->versionInfo->contentInfo->mainLanguageCode = 'eng-US';
        $content->versionInfo->contentInfo->remoteId = 'some_remote_id';
        $content->versionInfo->contentInfo->alwaysAvailable = true;
        $content->versionInfo->contentInfo->publicationDate = 123;
        $content->versionInfo->contentInfo->modificationDate = 456;
        $content->versionInfo->contentInfo->isPublished = false;
        $content->versionInfo->contentInfo->name = 'Content name';

        return $content;
    }

    /**
     * Returns a Version fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    protected function getVersionFixture()
    {
        $version = new VersionInfo();

        $version->id = null;
        $version->versionNo = 1;
        $version->creatorId = 13;
        $version->status = 0;
        $version->creationDate = 1312278322;
        $version->modificationDate = 1312278323;
        $version->initialLanguageCode = 'eng-GB';
        $version->contentInfo = new ContentInfo(
            [
                'id' => 2342,
                'alwaysAvailable' => true,
            ]
        );

        return $version;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::insertVersion
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::generateLanguageMask
     */
    public function testInsertVersion()
    {
        $version = $this->getVersionFixture();

        $gateway = $this->getDatabaseGateway();
        $gateway->insertVersion($version, []);

        $this->assertQueryResult(
            [
                [
                    'contentobject_id' => '2342',
                    'created' => '1312278322',
                    'creator_id' => '13',
                    'modified' => '1312278323',
                    'status' => '0',
                    'workflow_event_pos' => '0',
                    'version' => '1',
                    'language_mask' => '5',
                    'initial_language_id' => '4',
                    // Not needed, according to field mapping document
                    // 'user_id',
                ],
            ],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    [
                        'contentobject_id',
                        'created',
                        'creator_id',
                        'modified',
                        'status',
                        'workflow_event_pos',
                        'version',
                        'language_mask',
                        'initial_language_id',
                    ]
                )->from('ezcontentobject_version')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::setStatus
     */
    public function testSetStatus()
    {
        $gateway = $this->getDatabaseGateway();

        // insert content
        $struct = $this->getCreateStructFixture();
        $contentId = $gateway->insertContentObject($struct);

        // insert version
        $version = $this->getVersionFixture();
        $version->contentInfo->id = $contentId;
        $gateway->insertVersion($version, []);

        $this->assertTrue(
            $gateway->setStatus($version->contentInfo->id, $version->versionNo, VersionInfo::STATUS_PENDING)
        );

        $this->assertQueryResult(
            [[VersionInfo::STATUS_PENDING]],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('status')
                ->from('ezcontentobject_version')
        );

        // check that content status has not been set to published
        $this->assertQueryResult(
            [[VersionInfo::STATUS_DRAFT]],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('status')
                ->from('ezcontentobject')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::setStatus
     */
    public function testSetStatusPublished()
    {
        $gateway = $this->getDatabaseGateway();

        // insert content
        $struct = $this->getCreateStructFixture();
        $contentId = $gateway->insertContentObject($struct);

        // insert version
        $version = $this->getVersionFixture();
        $version->contentInfo->id = $contentId;
        $gateway->insertVersion($version, []);

        $this->assertTrue(
            $gateway->setStatus($version->contentInfo->id, $version->versionNo, VersionInfo::STATUS_PUBLISHED)
        );

        $this->assertQueryResult(
            [[VersionInfo::STATUS_PUBLISHED]],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('status')
                ->from('ezcontentobject_version')
        );

        // check that content status has been set to published
        $this->assertQueryResult(
            [[ContentInfo::STATUS_PUBLISHED]],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select('status')
                ->from('ezcontentobject')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::setStatus
     */
    public function testSetStatusUnknownVersion()
    {
        $gateway = $this->getDatabaseGateway();

        $this->assertFalse(
            $gateway->setStatus(23, 42, 2)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateContent
     */
    public function testUpdateContent()
    {
        $gateway = $this->getDatabaseGateway();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $metadataStruct = $this->getMetadataUpdateStructFixture();

        $gateway->updateContent(10, $metadataStruct);

        $this->assertQueryResult(
            [
                [
                    'initial_language_id' => '3',
                    'modified' => '234567',
                    'owner_id' => '42',
                    'published' => '123456',
                    'remote_id' => 'ghjk1234567890ghjk1234567890',
                    'name' => 'Thoth',
                ],
            ],
            $this->getDatabaseHandler()->createSelectQuery()
                ->select(
                    'initial_language_id',
                    'modified',
                    'owner_id',
                    'published',
                    'remote_id',
                    'name'
                )->from('ezcontentobject')
                ->where('id = 10')
        );
    }

    /**
     * Returns an UpdateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UpdateStruct
     */
    protected function getUpdateStructFixture()
    {
        $struct = new UpdateStruct();
        $struct->creatorId = 23;
        $struct->fields = [];
        $struct->modificationDate = 234567;
        $struct->initialLanguageId = 2;

        return $struct;
    }

    /**
     * Returns a MetadataUpdateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct
     */
    protected function getMetadataUpdateStructFixture()
    {
        $struct = new MetadataUpdateStruct();
        $struct->ownerId = 42;
        $struct->publicationDate = 123456;
        $struct->mainLanguageId = 3;
        $struct->modificationDate = 234567;
        $struct->remoteId = 'ghjk1234567890ghjk1234567890';
        $struct->name = 'Thoth';

        return $struct;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateVersion
     */
    public function testUpdateVersion()
    {
        $gateway = $this->getDatabaseGateway();

        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway->updateVersion(10, 2, $this->getUpdateStructFixture());

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [
                [
                    'creator_id' => '23',
                    'initial_language_id' => '2',
                    'modified' => '234567',
                ],
            ],
            $query
                ->select(
                    [
                        'creator_id',
                        'initial_language_id',
                        'modified',
                    ]
                )->from('ezcontentobject_version')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 10),
                        $query->expr->eq('version', 2)
                    )
                )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::insertNewField
     */
    public function testInsertNewField()
    {
        $content = $this->getContentFixture();
        $content->versionInfo->contentInfo->id = 2342;

        $field = $this->getFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = $this->getDatabaseGateway();
        $gateway->insertNewField($content, $field, $value);

        $this->assertQueryResult(
            [
                [
                    'contentclassattribute_id' => '231',
                    'contentobject_id' => '2342',
                    'data_float' => '24.42',
                    'data_int' => '42',
                    'data_text' => 'Test text',
                    'data_type_string' => 'ezstring',
                    'language_code' => 'eng-GB',
                    'language_id' => '4',
                    'sort_key_int' => '23',
                    'sort_key_string' => 'Test',
                    'version' => '1',
                ],
            ],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    [
                        'contentclassattribute_id',
                        'contentobject_id',
                        'data_float',
                        'data_int',
                        'data_text',
                        'data_type_string',
                        'language_code',
                        'language_id',
                        'sort_key_int',
                        'sort_key_string',
                        'version',
                    ]
                )->from('ezcontentobject_attribute')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::insertNewField
     */
    public function testInsertNewAlwaysAvailableField()
    {
        $content = $this->getContentFixture();
        $content->versionInfo->contentInfo->id = 2342;
        // Set main language to the one used in the field fixture
        $content->versionInfo->contentInfo->mainLanguageCode = 'eng-GB';

        $field = $this->getFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = $this->getDatabaseGateway();
        $gateway->insertNewField($content, $field, $value);

        $this->assertQueryResult(
            [
                [
                    'contentclassattribute_id' => '231',
                    'contentobject_id' => '2342',
                    'data_float' => '24.42',
                    'data_int' => '42',
                    'data_text' => 'Test text',
                    'data_type_string' => 'ezstring',
                    'language_code' => 'eng-GB',
                    'language_id' => '5',
                    'sort_key_int' => '23',
                    'sort_key_string' => 'Test',
                    'version' => '1',
                ],
            ],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    [
                        'contentclassattribute_id',
                        'contentobject_id',
                        'data_float',
                        'data_int',
                        'data_text',
                        'data_type_string',
                        'language_code',
                        'language_id',
                        'sort_key_int',
                        'sort_key_string',
                        'version',
                    ]
                )->from('ezcontentobject_attribute')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateField
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::setFieldUpdateValues
     */
    public function testUpdateField()
    {
        $content = $this->getContentFixture();
        $content->versionInfo->contentInfo->id = 2342;

        $field = $this->getFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = $this->getDatabaseGateway();
        $field->id = $gateway->insertNewField($content, $field, $value);

        $newValue = new StorageFieldValue(
            [
                'dataFloat' => 124.42,
                'dataInt' => 142,
                'dataText' => 'New text',
                'sortKeyInt' => 123,
                'sortKeyString' => 'new_text',
            ]
        );

        $gateway->updateField($field, $newValue);

        $this->assertQueryResult(
            [
                [
                    'data_float' => '124.42',
                    'data_int' => '142',
                    'data_text' => 'New text',
                    'sort_key_int' => '123',
                    'sort_key_string' => 'new_text',
                ],
            ],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    [
                        'data_float',
                        'data_int',
                        'data_text',
                        'sort_key_int',
                        'sort_key_string',
                    ]
                )->from('ezcontentobject_attribute')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateNonTranslatableField
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::setFieldUpdateValues
     */
    public function testUpdateNonTranslatableField()
    {
        $content = $this->getContentFixture();
        $content->versionInfo->contentInfo->id = 2342;

        $fieldGb = $this->getFieldFixture();
        $fieldUs = $this->getOtherLanguageFieldFixture();
        $value = $this->getStorageValueFixture();

        $gateway = $this->getDatabaseGateway();
        $fieldGb->id = $gateway->insertNewField($content, $fieldGb, $value);
        $fieldUs->id = $gateway->insertNewField($content, $fieldUs, $value);

        $updateStruct = new Content\UpdateStruct();

        $newValue = new StorageFieldValue(
            [
                'dataFloat' => 124.42,
                'dataInt' => 142,
                'dataText' => 'New text',
                'sortKeyInt' => 123,
                'sortKeyString' => 'new_text',
            ]
        );

        $gateway->updateNonTranslatableField($fieldGb, $newValue, $content->versionInfo->contentInfo->id);

        $this->assertQueryResult(
            [
                // Both fields updated
                [
                    'data_float' => '124.42',
                    'data_int' => '142',
                    'data_text' => 'New text',
                    'sort_key_int' => '123',
                    'sort_key_string' => 'new_text',
                ],
                [
                    'data_float' => '124.42',
                    'data_int' => '142',
                    'data_text' => 'New text',
                    'sort_key_int' => '123',
                    'sort_key_string' => 'new_text',
                ],
            ],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    [
                        'data_float',
                        'data_int',
                        'data_text',
                        'sort_key_int',
                        'sort_key_string',
                    ]
                )->from('ezcontentobject_attribute')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::listVersions
     */
    public function testListVersions()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->listVersions(226);

        $this->assertEquals(
            2,
            count($res)
        );

        foreach ($res as $row) {
            $this->assertEquals(
                22,
                count($row)
            );
        }

        $this->assertEquals(
            675,
            $res[0]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            676,
            $res[1]['ezcontentobject_version_id']
        );

        //$orig = include __DIR__ . '/../_fixtures/restricted_version_rows.php';

        /*$this->storeFixture(
            __DIR__ . '/../_fixtures/restricted_version_rows.php',
            $res
        );*/

        //$this->assertEquals( $orig, $res, "Fixtures differ between what was previously stored(expected) and what it now generates(actual), this hints either some mistake in impl or that the fixture (../_fixtures/restricted_version_rows.php) and tests needs to be adapted." );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::listVersionNumbers
     */
    public function testListVersionNumbers()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->listVersionNumbers(226);

        $this->assertEquals([1, 2], $res);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::listVersionsForUser
     */
    public function testListVersionsForUser()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->listVersionsForUser(14);

        $this->assertEquals(
            2,
            count($res)
        );

        foreach ($res as $row) {
            $this->assertEquals(
                22,
                count($row)
            );
        }

        $this->assertEquals(
            677,
            $res[0]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            0,
            $res[0]['ezcontentobject_version_status']
        );
        $this->assertEquals(
            678,
            $res[1]['ezcontentobject_version_id']
        );
        $this->assertEquals(
            0,
            $res[1]['ezcontentobject_version_status']
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::load
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder
     */
    public function testLoadWithAllTranslations()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->load(226, 2);

        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_code',
            ['eng-US', 'eng-GB'],
            $res
        );

        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_id',
            ['2', '4'],
            $res
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::load
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder
     */
    public function testCreateFixtureForMapperExtractContentFromRowsMultipleVersions()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $resFirst = $gateway->load(11, 1);
        $resSecond = $gateway->load(11, 2);

        $res = array_merge($resFirst, $resSecond);

        $orig = include __DIR__ . '/../_fixtures/extract_content_from_rows_multiple_versions.php';

        /*$this->storeFixture(
            __DIR__ . '/../_fixtures/extract_content_from_rows_multiple_versions.php',
            $res
        );*/

        $this->assertEquals($orig, $res, 'Fixtures differ between what was previously stored(expected) and what it now generates(actual), this hints either some mistake in impl or that the fixture (../_fixtures/extract_content_from_rows_multiple_versions.php) and tests needs to be adapted.');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::load
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder
     */
    public function testCreateFixtureForMapperExtractContentFromRows()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $res = $gateway->load(226, 2);

        $res = array_merge($res);

        $orig = include __DIR__ . '/../_fixtures/extract_content_from_rows.php';

        /*$this->storeFixture(
            __DIR__ . '/../_fixtures/extract_content_from_rows.php',
            $res
        );*/

        $this->assertEquals($orig, $res, 'Fixtures differ between what was previously stored(expected) and what it now generates(actual), this hints either some mistake in impl or that the fixture (../_fixtures/extract_content_from_rows.php) and tests needs to be adapted.');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::load
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder
     */
    public function testLoadWithSingleTranslation()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->load(226, 2, ['eng-GB']);

        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_code',
            ['eng-GB'],
            $res
        );
        $this->assertValuesInRows(
            'ezcontentobject_attribute_language_id',
            ['4'],
            $res
        );
        $this->assertEquals(
            1,
            count($res)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::load
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder
     */
    public function testLoadNonExistentTranslation()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $res = $gateway->load(226, 2, ['de-DE']);

        $this->assertEquals(
            0,
            count($res)
        );
    }

    /**
     * Asserts that $columnKey in $actualRows exactly contains $expectedValues.
     *
     * @param string $columnKey
     * @param string[] $expectedValues
     * @param string[][] $actualRows
     */
    protected function assertValuesInRows($columnKey, array $expectedValues, array $actualRows)
    {
        $expectedValues = array_fill_keys(
            array_values($expectedValues),
            true
        );

        $containedValues = [];

        foreach ($actualRows as $row) {
            if (isset($row[$columnKey])) {
                $containedValues[$row[$columnKey]] = true;
            }
        }

        $this->assertEquals(
            $expectedValues,
            $containedValues
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::getAllLocationIds
     */
    public function testGetAllLocationIds()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            [228],
            $gateway->getAllLocationIds(226)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::getFieldIdsByType
     */
    public function testGetFieldIdsByType()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            [
                'ezstring' => [841],
                'ezrichtext' => [842],
                'ezimage' => [843],
                'ezkeyword' => [844],
            ],
            $gateway->getFieldIdsByType(149)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::getFieldIdsByType
     */
    public function testGetFieldIdsByTypeWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            [
                'ezstring' => [4001, 4002],
            ],
            $gateway->getFieldIdsByType(225, 2)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteRelations
     */
    public function testDeleteRelationsTo()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentRelations(),
            'from' => $this->countContentRelations(149),
            'to' => $this->countContentRelations(null, 149),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelations(149);

        $this->assertEquals(
            // yes, relates to itself!
            [
                'all' => $beforeCount['all'] - 2,
                'from' => $beforeCount['from'] - 1,
                'to' => $beforeCount['to'] - 2,
            ],
            [
                'all' => $this->countContentRelations(),
                'from' => $this->countContentRelations(149),
                'to' => $this->countContentRelations(null, 149),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteRelations
     */
    public function testDeleteRelationsFrom()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentRelations(),
            'from' => $this->countContentRelations(75),
            'to' => $this->countContentRelations(null, 75),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelations(75);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 6,
                'from' => $beforeCount['from'] - 6,
                'to' => $beforeCount['to'],
            ],
            [
                'all' => $this->countContentRelations(),
                'from' => $this->countContentRelations(75),
                'to' => $this->countContentRelations(null, 75),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteRelations
     */
    public function testDeleteRelationsWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentRelations(),
            'from' => $this->countContentRelations(225),
            'to' => $this->countContentRelations(null, 225),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelations(225, 2);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 1,
                'from' => $beforeCount['from'] - 1,
                'to' => $beforeCount['to'],
            ],
            [
                'all' => $this->countContentRelations(),
                'from' => $this->countContentRelations(225),
                'to' => $this->countContentRelations(null, 225),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteField
     */
    public function testDeleteField()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = $this->countContentFields();

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteField(22);

        $this->assertEquals(
            $beforeCount - 2,
            $this->countContentFields()
        );

        $this->assertQueryResult(
            [],
            $this->getDatabaseHandler()->createSelectQuery()
                ->select('*')
                ->from('ezcontentobject_attribute')
                ->where('id=22')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteFields
     */
    public function testDeleteFields()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentFields(),
            'this' => $this->countContentFields(4),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteFields(4);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 2,
                'this' => 0,
            ],
            [
                'all' => $this->countContentFields(),
                'this' => $this->countContentFields(4),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteFields
     */
    public function testDeleteFieldsWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentFields(),
            'this' => $this->countContentFields(225),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteFields(225, 2);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 2,
                'this' => $beforeCount['this'] - 2,
            ],
            [
                'all' => $this->countContentFields(),
                'this' => $this->countContentFields(225),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteVersions
     */
    public function testDeleteVersions()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentVersions(),
            'this' => $this->countContentVersions(14),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteVersions(14);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 2,
                'this' => 0,
            ],
            [
                'all' => $this->countContentVersions(),
                'this' => $this->countContentVersions(14),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteVersions
     */
    public function testDeleteVersionsWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentVersions(),
            'this' => $this->countContentVersions(225),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteVersions(225, 2);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 1,
                'this' => $beforeCount['this'] - 1,
            ],
            [
                'all' => $this->countContentVersions(),
                'this' => $this->countContentVersions(225),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::setName
     */
    public function testSetName()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $gateway->setName(14, 2, 'Hello world!', 'eng-GB');

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [['eng-GB', 2, 14, 4, 'Hello world!', 'eng-GB']],
            $query->select('*')
                ->from('ezcontentobject_name')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 14),
                        $query->expr->eq('content_version', 2),
                        $query->expr->eq('content_translation', $query->bindValue('eng-GB'))
                    )
                )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteNames
     */
    public function testDeleteNames()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentNames(),
            'this' => $this->countContentNames(14),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteNames(14);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 2,
                'this' => 0,
            ],
            [
                'all' => $this->countContentNames(),
                'this' => $this->countContentNames(14),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteNames
     */
    public function testDeleteNamesWithSecondArgument()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = [
            'all' => $this->countContentNames(),
            'this' => $this->countContentNames(225),
        ];

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteNames(225, 2);

        $this->assertEquals(
            [
                'all' => $beforeCount['all'] - 1,
                'this' => $beforeCount['this'] - 1,
            ],
            [
                'all' => $this->countContentNames(),
                'this' => $this->countContentNames(225),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteContent
     */
    public function testDeleteContent()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $beforeCount = $this->countContent();

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteContent(14);

        $this->assertEquals(
            [
                'all' => $beforeCount - 1,
                'this' => 0,
            ],
            [
                'all' => $this->countContent(),
                'this' => $this->countContent(14),
            ]
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::loadRelations
     */
    public function testLoadRelations()
    {
        $this->insertRelationFixture();

        $gateway = $this->getDatabaseGateway();

        $relations = $gateway->loadRelations(57);

        $this->assertEquals(3, count($relations));

        $this->assertValuesInRows(
            'ezcontentobject_link_to_contentobject_id',
            [58, 59, 60],
            $relations
        );

        $this->assertValuesInRows(
            'ezcontentobject_link_from_contentobject_id',
            [57],
            $relations
        );
        $this->assertValuesInRows(
            'ezcontentobject_link_from_contentobject_version',
            [2],
            $relations
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::loadRelations
     */
    public function testLoadRelationsByType()
    {
        $this->insertRelationFixture();

        $gateway = $this->getDatabaseGateway();

        $relations = $gateway->loadRelations(57, null, \eZ\Publish\API\Repository\Values\Content\Relation::COMMON);

        $this->assertEquals(1, count($relations), 'Expecting one relation to be loaded');

        $this->assertValuesInRows(
            'ezcontentobject_link_relation_type',
            [\eZ\Publish\API\Repository\Values\Content\Relation::COMMON],
            $relations
        );

        $this->assertValuesInRows(
            'ezcontentobject_link_to_contentobject_id',
            [58],
            $relations
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::loadRelations
     */
    public function testLoadRelationsByVersion()
    {
        $this->insertRelationFixture();

        $gateway = $this->getDatabaseGateway();

        $relations = $gateway->loadRelations(57, 1);

        $this->assertEquals(1, count($relations), 'Expecting one relation to be loaded');

        $this->assertValuesInRows(
            'ezcontentobject_link_to_contentobject_id',
            [58],
            $relations
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::loadRelations
     */
    public function testLoadRelationsNoResult()
    {
        $this->insertRelationFixture();

        $gateway = $this->getDatabaseGateway();

        $relations = $gateway->loadRelations(57, 1, \eZ\Publish\API\Repository\Values\Content\Relation::EMBED);

        $this->assertEquals(0, count($relations), 'Expecting no relation to be loaded');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::loadReverseRelations
     */
    public function testLoadReverseRelations()
    {
        $this->insertRelationFixture();

        $gateway = $this->getDatabaseGateway();

        $relations = $gateway->loadReverseRelations(58);

        self::assertEquals(2, count($relations));

        $this->assertValuesInRows(
            'ezcontentobject_link_from_contentobject_id',
            [57, 61],
            $relations
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::loadReverseRelations
     */
    public function testLoadReverseRelationsWithType()
    {
        $this->insertRelationFixture();

        $gateway = $this->getDatabaseGateway();

        $relations = $gateway->loadReverseRelations(58, \eZ\Publish\API\Repository\Values\Content\Relation::COMMON);

        self::assertEquals(1, count($relations));

        $this->assertValuesInRows(
            'ezcontentobject_link_from_contentobject_id',
            [57],
            $relations
        );

        $this->assertValuesInRows(
            'ezcontentobject_link_relation_type',
            [\eZ\Publish\API\Repository\Values\Content\Relation::COMMON],
            $relations
        );
    }

    /**
     * Inserts the relation database fixture from relation_data.php.
     */
    protected function insertRelationFixture()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/relations_data.php'
        );
    }

    /*
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::getLastVersionNumber
     *
     * @return void
     */
    public function testGetLastVersionNumber()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $this->assertEquals(
            1,
            $gateway->getLastVersionNumber(4)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::insertRelation
     */
    public function testInsertRelation()
    {
        $struct = $this->getRelationCreateStructFixture();
        $gateway = $this->getDatabaseGateway();
        $gateway->insertRelation($struct);

        $this->assertQueryResult(
            [
                [
                    'id' => 1,
                    'from_contentobject_id' => $struct->sourceContentId,
                    'from_contentobject_version' => $struct->sourceContentVersionNo,
                    'contentclassattribute_id' => $struct->sourceFieldDefinitionId,
                    'to_contentobject_id' => $struct->destinationContentId,
                    'relation_type' => $struct->type,
                ],
            ],
            $this->getDatabaseHandler()
                ->createSelectQuery()
                ->select(
                    [
                        'id',
                        'from_contentobject_id',
                        'from_contentobject_version',
                        'contentclassattribute_id',
                        'to_contentobject_id',
                        'relation_type',
                    ]
                )->from('ezcontentobject_link')
                ->where('id = 1')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteRelation
     */
    public function testDeleteRelation()
    {
        $this->insertRelationFixture();

        self::assertEquals(4, $this->countContentRelations(57));

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelation(2, RelationValue::COMMON);

        self::assertEquals(3, $this->countContentRelations(57));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::deleteRelation
     */
    public function testDeleteRelationWithCompositeBitmask()
    {
        $this->insertRelationFixture();

        $gateway = $this->getDatabaseGateway();
        $gateway->deleteRelation(11, RelationValue::COMMON);

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [['relation_type' => RelationValue::LINK]],
            $query->select(
                ['relation_type']
            )->from(
                'ezcontentobject_link'
            )->where(
                $query->expr->eq('id', 11)
            )
        );
    }

    /**
     * Test for the updateAlwaysAvailableFlag() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateAlwaysAvailableFlag
     */
    public function testUpdateAlwaysAvailableFlagRemove()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->updateAlwaysAvailableFlag(103, false);

        $this->assertQueryResult(
            [['id' => 2]],
            $this->getDatabaseHandler()->createSelectQuery()->select(
                ['language_mask']
            )->from(
                'ezcontentobject'
            )->where(
                'id = 103'
            )
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [['language_id' => 2]],
            $query->select(
                ['language_id']
            )->from(
                'ezcontentobject_name'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 103),
                    $query->expr->eq('content_version', 1)
                )
            )
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [
                ['language_id' => 2],
            ],
            $query->selectDistinct(
                ['language_id']
            )->from(
                'ezcontentobject_attribute'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 103),
                    $query->expr->eq('version', 1)
                )
            )
        );
    }

    /**
     * Test for the updateAlwaysAvailableFlag() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateAlwaysAvailableFlag
     */
    public function testUpdateAlwaysAvailableFlagAdd()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();
        $gateway->updateAlwaysAvailableFlag(102, true);

        $this->assertQueryResult(
            [['id' => 3]],
            $this->getDatabaseHandler()->createSelectQuery()->select(
                ['language_mask']
            )->from(
                'ezcontentobject'
            )->where(
                'id = 102'
            )
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [['language_id' => 3]],
            $query->select(
                ['language_id']
            )->from(
                'ezcontentobject_name'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 102),
                    $query->expr->eq('content_version', 1)
                )
            )
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [
                ['language_id' => 3],
            ],
            $query->selectDistinct(
                ['language_id']
            )->from(
                'ezcontentobject_attribute'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 102),
                    $query->expr->eq('version', 1)
                )
            )
        );
    }

    /**
     * Test for the updateAlwaysAvailableFlag() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateAlwaysAvailableFlag
     */
    public function testUpdateContentAddAlwaysAvailableFlagMultilingual()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects_multilingual.php'
        );

        $gateway = $this->getDatabaseGateway();
        $contentMetadataUpdateStruct = new MetadataUpdateStruct(
            [
                'mainLanguageId' => 4,
                'alwaysAvailable' => true,
            ]
        );
        $gateway->updateContent(4, $contentMetadataUpdateStruct);

        $this->assertQueryResult(
            [['id' => 7]],
            $this->getDatabaseHandler()->createSelectQuery()->select(
                ['language_mask']
            )->from(
                'ezcontentobject'
            )->where(
                'id = 4'
            )
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [
                ['id' => '7', 'language_id' => 2],
                ['id' => '8', 'language_id' => 5],
            ],
            $query->selectDistinct(
                ['id', 'language_id']
            )->from(
                'ezcontentobject_attribute'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 4),
                    $query->expr->eq('version', 2)
                )
            )->orderBy('id')
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [
                ['id' => '7', 'language_id' => 2],
                ['id' => '8', 'language_id' => 5],
            ],
            $query->selectDistinct(
                ['id', 'language_id']
            )->from(
                'ezcontentobject_attribute'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 4),
                    $query->expr->eq('version', 1)
                )
            )->orderBy('id')
        );
    }

    /**
     * Test for the updateAlwaysAvailableFlag() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase::updateAlwaysAvailableFlag
     */
    public function testUpdateContentRemoveAlwaysAvailableFlagMultilingual()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects_multilingual.php'
        );

        $gateway = $this->getDatabaseGateway();
        $contentMetadataUpdateStruct = new MetadataUpdateStruct(
            [
                'mainLanguageId' => 4,
                'alwaysAvailable' => false,
            ]
        );
        $gateway->updateContent(4, $contentMetadataUpdateStruct);

        $this->assertQueryResult(
            [['id' => 6]],
            $this->getDatabaseHandler()->createSelectQuery()->select(
                ['language_mask']
            )->from(
                'ezcontentobject'
            )->where(
                'id = 4'
            )
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [
                ['id' => '7', 'language_id' => 2],
                ['id' => '8', 'language_id' => 4],
            ],
            $query->selectDistinct(
                ['id', 'language_id']
            )->from(
                'ezcontentobject_attribute'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 4),
                    $query->expr->eq('version', 2)
                )
            )->orderBy('id')
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $this->assertQueryResult(
            [
                ['id' => '7', 'language_id' => 2],
                ['id' => '8', 'language_id' => 5],
            ],
            $query->selectDistinct(
                ['id', 'language_id']
            )->from(
                'ezcontentobject_attribute'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 4),
                    $query->expr->eq('version', 1)
                )
            )->orderBy('id')
        );
    }

    public function testLoadVersionInfo()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $resFirst = $gateway->loadVersionInfo(11, 1);
        $resSecond = $gateway->loadVersionInfo(11, 2);

        $res = array_merge($resFirst, $resSecond);

        $orig = include __DIR__ . '/../_fixtures/extract_version_info_from_rows_multiple_versions.php';

        /*$this->storeFixture(
            __DIR__ . '/../_fixtures/extract_version_info_from_rows_multiple_versions.php',
            $res
        );*/

        $this->assertEquals($orig, $res, 'Fixtures differ between what was previously stored(expected) and what it now generates(actual), this hints either some mistake in impl or that the fixture (../_fixtures/extract_content_from_rows_multiple_versions.php) and tests needs to be adapted.');
    }

    /**
     * Counts the number of relations in the database.
     *
     * @param int $fromId
     * @param int $toId
     *
     * @return int
     */
    protected function countContentRelations($fromId = null, $toId = null)
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select('count(*)')
            ->from('ezcontentobject_link');

        if ($fromId !== null) {
            $query->where(
                'from_contentobject_id=' . $fromId
            );
        }
        if ($toId !== null) {
            $query->where(
                'to_contentobject_id=' . $toId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of fields.
     *
     * @param int $contentId
     *
     * @return int
     */
    protected function countContentFields($contentId = null)
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select('count(*)')
            ->from('ezcontentobject_attribute');

        if ($contentId !== null) {
            $query->where(
                'contentobject_id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of versions.
     *
     * @param int $contentId
     *
     * @return int
     */
    protected function countContentVersions($contentId = null)
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select('count(*)')
            ->from('ezcontentobject_version');

        if ($contentId !== null) {
            $query->where(
                'contentobject_id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of content names.
     *
     * @param int $contentId
     *
     * @return int
     */
    protected function countContentNames($contentId = null)
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select('count(*)')
            ->from('ezcontentobject_name');

        if ($contentId !== null) {
            $query->where(
                'contentobject_id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of content objects.
     *
     * @param int $contentId
     *
     * @return int
     */
    protected function countContent($contentId = null)
    {
        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query->select('count(*)')
            ->from('ezcontentobject');

        if ($contentId !== null) {
            $query->where(
                'id=' . $contentId
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Stores $fixture in $file to be required as a fixture.
     *
     * @param string $file
     * @param mixed $fixture
     */
    protected function storeFixture($file, $fixture)
    {
        file_put_contents(
            $file,
            "<?php\n\nreturn " . str_replace(" \n", "\n", var_export($fixture, true)) . ";\n"
        );
    }

    /**
     * Returns a Field fixture.
     *
     * @return Field
     */
    protected function getFieldFixture()
    {
        $field = new Field();

        $field->fieldDefinitionId = 231;
        $field->type = 'ezstring';
        $field->languageCode = 'eng-GB';
        $field->versionNo = 1;

        return $field;
    }

    /**
     * Returns a Field fixture in a different language.
     *
     * @return Field
     */
    protected function getOtherLanguageFieldFixture()
    {
        $field = $this->getFieldFixture();
        $field->languageCode = 'eng-US';

        return $field;
    }

    /**
     * Returns a StorageFieldValue fixture.
     *
     * @return StorageFieldValue
     */
    protected function getStorageValueFixture()
    {
        $value = new StorageFieldValue();

        $value->dataFloat = 24.42;
        $value->dataInt = 42;
        $value->dataText = 'Test text';
        $value->sortKeyInt = 23;
        $value->sortKeyString = 'Test';

        return $value;
    }

    /**
     * Returns a ready to test DoctrineDatabase gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase
     */
    protected function getDatabaseGateway()
    {
        if (!isset($this->databaseGateway)) {
            $this->databaseGateway = new DoctrineDatabase(
                ($dbHandler = $this->getDatabaseHandler()),
                $this->getDatabaseConnection(),
                new DoctrineDatabase\QueryBuilder($dbHandler),
                $this->getLanguageHandler(),
                $this->getLanguageMaskGenerator()
            );
        }

        return $this->databaseGateway;
    }

    /**
     * DoctrineDatabaseTest::getRelationCreateStructFixture().
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct
     */
    protected function getRelationCreateStructFixture()
    {
        $struct = new RelationCreateStruct();

        $struct->destinationContentId = 1;
        $struct->sourceContentId = 1;
        $struct->sourceContentVersionNo = 1;
        $struct->sourceFieldDefinitionId = 0;
        $struct->type = RelationValue::COMMON;

        return $struct;
    }
}
