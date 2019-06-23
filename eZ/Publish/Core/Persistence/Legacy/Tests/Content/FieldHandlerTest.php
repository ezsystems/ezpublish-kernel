<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\FieldType\FieldType;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

/**
 * Test case for Content Handler.
 */
class FieldHandlerTest extends LanguageAwareTestCase
{
    /**
     * Gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * Mapper mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $mapperMock;

    /**
     * Storage handler mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandlerMock;

    /**
     * Field type registry mock.
     *
     * @var \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    protected $fieldTypeRegistryMock;

    /**
     * Field type mock.
     *
     * @var \eZ\Publish\SPI\FieldType\FieldType
     */
    protected $fieldTypeMock;

    /**
     * @param bool $storageHandlerUpdatesFields
     */
    protected function assertCreateNewFields($storageHandlerUpdatesFields = false)
    {
        $contentGatewayMock = $this->getContentGatewayMock();
        $fieldTypeMock = $this->getFieldTypeMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $fieldTypeMock->expects($this->exactly(3))
            ->method('getEmptyValue')
            ->will($this->returnValue(new FieldValue()));

        $contentGatewayMock->expects($this->exactly(6))
            ->method('insertNewField')
            ->with(
                $this->isInstanceOf(Content::class),
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            )->will($this->returnValue(42));

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ([1, 2, 3] as $fieldDefinitionId) {
            foreach (['eng-US', 'eng-GB'] as $languageCode) {
                $field = new Field(
                    [
                        'id' => 42,
                        'fieldDefinitionId' => $fieldDefinitionId,
                        'type' => 'some-type',
                        'versionNo' => 1,
                        'value' => $fieldValue,
                        'languageCode' => $languageCode,
                    ]
                );
                // This field is copied from main language
                if ($fieldDefinitionId == 2 && $languageCode == 'eng-US') {
                    $copyField = clone $field;
                    $originalField = clone $field;
                    $originalField->languageCode = 'eng-GB';
                    continue;
                }
                $storageHandlerMock->expects($this->at($callNo++))
                    ->method('storeFieldData')
                    ->with(
                        $this->isInstanceOf(VersionInfo::class),
                        $this->equalTo($field)
                    )->will($this->returnValue($storageHandlerUpdatesFields));
            }
        }

        /* @var $copyField */
        /* @var $originalField */
        $storageHandlerMock->expects($this->at($callNo))
            ->method('copyFieldData')
            ->with(
                $this->isInstanceOf(VersionInfo::class),
                $this->equalTo($copyField),
                $this->equalTo($originalField)
            )->will($this->returnValue($storageHandlerUpdatesFields));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     */
    public function testCreateNewFields()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateNewFields(false);

        $mapperMock->expects($this->exactly(6))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $fieldHandler->createNewFields(
            $this->getContentPartialFieldsFixture(),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     */
    public function testCreateNewFieldsUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $contentGatewayMock = $this->getContentGatewayMock();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateNewFields(true);

        $mapperMock->expects($this->exactly(12))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $contentGatewayMock->expects($this->exactly(6))
            ->method('updateField')
            ->with(
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $fieldHandler->createNewFields(
            $this->getContentPartialFieldsFixture(),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @param bool $storageHandlerUpdatesFields
     */
    protected function assertCreateNewFieldsForMainLanguage($storageHandlerUpdatesFields = false)
    {
        $contentGatewayMock = $this->getContentGatewayMock();
        $fieldTypeMock = $this->getFieldTypeMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $fieldTypeMock->expects($this->exactly(3))
            ->method('getEmptyValue')
            ->will($this->returnValue(new FieldValue()));

        $contentGatewayMock->expects($this->exactly(3))
            ->method('insertNewField')
            ->with(
                $this->isInstanceOf(Content::class),
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            )->will($this->returnValue(42));

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ([1, 2, 3] as $fieldDefinitionId) {
            $field = new Field(
                [
                    'id' => 42,
                    'fieldDefinitionId' => $fieldDefinitionId,
                    'type' => 'some-type',
                    'versionNo' => 1,
                    'value' => $fieldValue,
                    'languageCode' => 'eng-GB',
                ]
            );
            $storageHandlerMock->expects($this->at($callNo++))
                ->method('storeFieldData')
                ->with(
                    $this->isInstanceOf(VersionInfo::class),
                    $this->equalTo($field)
                )->will($this->returnValue($storageHandlerUpdatesFields));
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     */
    public function testCreateNewFieldsForMainLanguage()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateNewFieldsForMainLanguage(false);

        $mapperMock->expects($this->exactly(3))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $fieldHandler->createNewFields(
            $this->getContentNoFieldsFixture(),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createNewFields
     */
    public function testCreateNewFieldsForMainLanguageUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $contentGatewayMock = $this->getContentGatewayMock();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateNewFieldsForMainLanguage(true);

        $mapperMock->expects($this->exactly(6))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $contentGatewayMock->expects($this->exactly(3))
            ->method('updateField')
            ->with(
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $fieldHandler->createNewFields(
            $this->getContentNoFieldsFixture(),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @param bool $storageHandlerUpdatesFields
     */
    protected function assertCreateExistingFieldsInNewVersion($storageHandlerUpdatesFields = false)
    {
        $contentGatewayMock = $this->getContentGatewayMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $contentGatewayMock->expects($this->exactly(6))
            ->method('insertExistingField')
            ->with(
                $this->isInstanceOf(Content::class),
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            )->will($this->returnValue(42));

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ([1, 2, 3] as $fieldDefinitionId) {
            foreach (['eng-US', 'eng-GB'] as $languageIndex => $languageCode) {
                $field = new Field(
                    [
                        'id' => $fieldDefinitionId * 10 + $languageIndex + 1,
                        'fieldDefinitionId' => $fieldDefinitionId,
                        'type' => 'some-type',
                        'value' => $fieldValue,
                        'languageCode' => $languageCode,
                    ]
                );
                $originalField = clone $field;
                $field->versionNo = 1;
                $storageHandlerMock->expects($this->at($callNo++))
                    ->method('copyFieldData')
                    ->with(
                        $this->isInstanceOf(VersionInfo::class),
                        $this->equalTo($field),
                        $this->equalTo($originalField)
                    )->will($this->returnValue($storageHandlerUpdatesFields));
            }
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createExistingFieldsInNewVersion
     */
    public function testCreateExistingFieldsInNewVersion()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateExistingFieldsInNewVersion(false);

        $mapperMock->expects($this->exactly(6))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $fieldHandler->createExistingFieldsInNewVersion($this->getContentFixture());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::createExistingFieldsInNewVersion
     */
    public function testCreateExistingFieldsInNewVersionUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $contentGatewayMock = $this->getContentGatewayMock();
        $mapperMock = $this->getMapperMock();

        $this->assertCreateExistingFieldsInNewVersion(true);

        $mapperMock->expects($this->exactly(12))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $contentGatewayMock->expects($this->exactly(6))
            ->method('updateField')
            ->with(
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $fieldHandler->createExistingFieldsInNewVersion($this->getContentFixture());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::loadExternalFieldData
     */
    public function testLoadExternalFieldData()
    {
        $fieldHandler = $this->getFieldHandler();

        $storageHandlerMock = $this->getStorageHandlerMock();

        $storageHandlerMock->expects($this->exactly(6))
            ->method('getFieldData')
            ->with(
                $this->isInstanceOf(VersionInfo::class),
                $this->isInstanceOf(Field::class)
            );

        $fieldHandler->loadExternalFieldData($this->getContentFixture());
    }

    /**
     * @param bool $storageHandlerUpdatesFields
     */
    public function assertUpdateFieldsWithNewLanguage($storageHandlerUpdatesFields = false)
    {
        $contentGatewayMock = $this->getContentGatewayMock();
        $fieldTypeMock = $this->getFieldTypeMock();
        $storageHandlerMock = $this->getStorageHandlerMock();

        $fieldTypeMock->expects($this->exactly(1))
            ->method('getEmptyValue')
            ->will($this->returnValue(new FieldValue()));

        $contentGatewayMock->expects($this->exactly(3))
            ->method('insertNewField')
            ->with(
                $this->isInstanceOf(Content::class),
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $callNo = 0;
        $fieldValue = new FieldValue();
        foreach ([1, 2, 3] as $fieldDefinitionId) {
            $field = new Field(
                [
                    'fieldDefinitionId' => $fieldDefinitionId,
                    'type' => 'some-type',
                    'versionNo' => 1,
                    'value' => $fieldValue,
                    'languageCode' => 'ger-DE',
                ]
            );
            // This field is copied from main language
            if ($fieldDefinitionId == 3) {
                $copyField = clone $field;
                $originalField = clone $field;
                $originalField->id = $fieldDefinitionId * 10 + 2;
                $originalField->languageCode = 'eng-GB';
                continue;
            }
            $storageHandlerMock->expects($this->at($callNo++))
                ->method('storeFieldData')
                ->with(
                    $this->isInstanceOf(VersionInfo::class),
                    $this->equalTo($field)
                )->will($this->returnValue($storageHandlerUpdatesFields));
        }

        /* @var $copyField */
        /* @var $originalField */
        $storageHandlerMock->expects($this->at($callNo))
            ->method('copyFieldData')
            ->with(
                $this->isInstanceOf(VersionInfo::class),
                $this->equalTo($copyField),
                $this->equalTo($originalField)
            )->will($this->returnValue($storageHandlerUpdatesFields));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsWithNewLanguage()
    {
        $mapperMock = $this->getMapperMock();
        $fieldHandler = $this->getFieldHandler();

        $this->assertUpdateFieldsWithNewLanguage(false);

        $mapperMock->expects($this->exactly(3))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $field = new Field(
            [
                'type' => 'some-type',
                'value' => new FieldValue(),
                'fieldDefinitionId' => 2,
                'languageCode' => 'ger-DE',
            ]
        );
        $fieldHandler->updateFields(
            $this->getContentFixture(),
            new UpdateStruct(
                [
                    'initialLanguageId' => 8,
                    'fields' => [$field],
                ]
            ),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsWithNewLanguageUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();
        $contentGatewayMock = $this->getContentGatewayMock();

        $this->assertUpdateFieldsWithNewLanguage(true);

        $mapperMock->expects($this->exactly(6))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $contentGatewayMock->expects($this->exactly(3))
            ->method('updateField')
            ->with(
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $field = new Field(
            [
                'type' => 'some-type',
                'value' => new FieldValue(),
                'fieldDefinitionId' => 2,
                'languageCode' => 'ger-DE',
            ]
        );
        $fieldHandler->updateFields(
            $this->getContentFixture(),
            new UpdateStruct(
                [
                    'initialLanguageId' => 8,
                    'fields' => [$field],
                ]
            ),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @param bool $storageHandlerUpdatesFields
     */
    public function assertUpdateFieldsExistingLanguages($storageHandlerUpdatesFields = false)
    {
        $storageHandlerMock = $this->getStorageHandlerMock();

        $callNo = 0;
        $fieldValue = new FieldValue();
        $fieldsToCopy = [];
        foreach ([1, 2, 3] as $fieldDefinitionId) {
            foreach (['eng-US', 'eng-GB'] as $languageIndex => $languageCode) {
                $field = new Field(
                    [
                        'id' => $fieldDefinitionId * 10 + $languageIndex + 1,
                        'fieldDefinitionId' => $fieldDefinitionId,
                        'type' => 'some-type',
                        'versionNo' => 1,
                        'value' => $fieldValue,
                        'languageCode' => $languageCode,
                    ]
                );
                // These fields are copied from main language
                if (($fieldDefinitionId == 2 || $fieldDefinitionId == 3) && $languageCode != 'eng-GB') {
                    $originalField = clone $field;
                    $originalField->id = $fieldDefinitionId * 10 + $languageIndex + 2;
                    $originalField->languageCode = 'eng-GB';
                    $fieldsToCopy[] = [
                        'copy' => clone $field,
                        'original' => $originalField,
                    ];
                } else {
                    $storageHandlerMock->expects($this->at($callNo++))
                        ->method('storeFieldData')
                        ->with(
                            $this->isInstanceOf(VersionInfo::class),
                            $this->equalTo($field)
                        )->will($this->returnValue($storageHandlerUpdatesFields));
                }
            }
        }

        foreach ($fieldsToCopy as $fieldToCopy) {
            $storageHandlerMock->expects($this->at($callNo++))
                ->method('copyFieldData')
                ->with(
                    $this->isInstanceOf(VersionInfo::class),
                    $this->equalTo($fieldToCopy['copy']),
                    $this->equalTo($fieldToCopy['original'])
                )->will($this->returnValue($storageHandlerUpdatesFields));
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsExistingLanguages()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();
        $contentGatewayMock = $this->getContentGatewayMock();

        $this->assertUpdateFieldsExistingLanguages(false);

        $mapperMock->expects($this->exactly(6))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $contentGatewayMock->expects($this->exactly(6))
            ->method('updateField')
            ->with(
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $fieldHandler->updateFields(
            $this->getContentFixture(),
            $this->getUpdateStructFixture(),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsExistingLanguagesUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();
        $contentGatewayMock = $this->getContentGatewayMock();

        $this->assertUpdateFieldsExistingLanguages(true);

        $mapperMock->expects($this->exactly(12))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $contentGatewayMock->expects($this->exactly(12))
            ->method('updateField')
            ->with(
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $fieldHandler->updateFields(
            $this->getContentFixture(),
            $this->getUpdateStructFixture(),
            $this->getContentTypeFixture()
        );
    }

    /**
     * @param bool $storageHandlerUpdatesFields
     */
    public function assertUpdateFieldsForInitialLanguage($storageHandlerUpdatesFields = false)
    {
        $storageHandlerMock = $this->getStorageHandlerMock();

        $callNo = 0;
        $fieldValue = new FieldValue();
        $fieldsToCopy = [];
        foreach ([1, 2, 3] as $fieldDefinitionId) {
            $field = new Field(
                [
                    'fieldDefinitionId' => $fieldDefinitionId,
                    'type' => 'some-type',
                    'versionNo' => 1,
                    'value' => $fieldValue,
                    'languageCode' => 'eng-US',
                ]
            );
            // These fields are copied from main language
            if ($fieldDefinitionId == 2 || $fieldDefinitionId == 3) {
                $originalField = clone $field;
                $originalField->languageCode = 'eng-GB';
                $fieldsToCopy[] = [
                    'copy' => clone $field,
                    'original' => $originalField,
                ];
                continue;
            }
            // This field is inserted as empty
            $field->value = null;
            $storageHandlerMock->expects($this->at($callNo++))
                ->method('storeFieldData')
                ->with(
                    $this->isInstanceOf(VersionInfo::class),
                    $this->equalTo($field)
                )->will($this->returnValue($storageHandlerUpdatesFields));
        }

        foreach ($fieldsToCopy as $fieldToCopy) {
            $storageHandlerMock->expects($this->at($callNo++))
                ->method('copyFieldData')
                ->with(
                    $this->isInstanceOf(VersionInfo::class),
                    $this->equalTo($fieldToCopy['copy']),
                    $this->equalTo($fieldToCopy['original'])
                )->will($this->returnValue($storageHandlerUpdatesFields));
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsForInitialLanguage()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();

        $this->assertUpdateFieldsForInitialLanguage(false);

        $mapperMock->expects($this->exactly(3))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $struct = new UpdateStruct();
        // Language with id=2 is eng-US
        $struct->initialLanguageId = 2;
        $fieldHandler->updateFields(
            $this->getContentSingleLanguageFixture(),
            $struct,
            $this->getContentTypeFixture()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::updateFields
     */
    public function testUpdateFieldsForInitialLanguageUpdatingStorageHandler()
    {
        $fieldHandler = $this->getFieldHandler();
        $mapperMock = $this->getMapperMock();
        $contentGatewayMock = $this->getContentGatewayMock();

        $this->assertUpdateFieldsForInitialLanguage(true);

        $mapperMock->expects($this->exactly(6))
            ->method('convertToStorageValue')
            ->with($this->isInstanceOf(Field::class))
            ->will($this->returnValue(new StorageFieldValue()));

        $contentGatewayMock->expects($this->exactly(3))
            ->method('updateField')
            ->with(
                $this->isInstanceOf(Field::class),
                $this->isInstanceOf(StorageFieldValue::class)
            );

        $struct = new UpdateStruct();
        // Language with id=2 is eng-US
        $struct->initialLanguageId = 2;
        $fieldHandler->updateFields(
            $this->getContentSingleLanguageFixture(),
            $struct,
            $this->getContentTypeFixture()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler::deleteFields
     */
    public function testDeleteFields()
    {
        $fieldHandler = $this->getFieldHandler();

        $contentGatewayMock = $this->getContentGatewayMock();
        $contentGatewayMock->expects($this->once())
            ->method('getFieldIdsByType')
            ->with(
                $this->equalTo(42),
                $this->equalTo(2)
            )->will($this->returnValue(['some-type' => [2, 3]]));

        $storageHandlerMock = $this->getStorageHandlerMock();
        $storageHandlerMock->expects($this->once())
            ->method('deleteFieldData')
            ->with(
                $this->equalTo('some-type'),
                $this->isInstanceOf(VersionInfo::class),
                $this->equalTo([2, 3])
            );

        $contentGatewayMock->expects($this->once())
            ->method('deleteFields')
            ->with(
                $this->equalTo(42),
                $this->equalTo(2)
            );

        $fieldHandler->deleteFields(42, new VersionInfo(['versionNo' => 2]));
    }

    /**
     * Returns a Content fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentPartialFieldsFixture()
    {
        $content = new Content();
        $content->versionInfo = new VersionInfo();
        $content->versionInfo->versionNo = 1;
        $content->versionInfo->languageCodes = ['eng-US', 'eng-GB'];
        $content->versionInfo->contentInfo = new ContentInfo();
        $content->versionInfo->contentInfo->id = 42;
        $content->versionInfo->contentInfo->contentTypeId = 1;
        $content->versionInfo->contentInfo->mainLanguageCode = 'eng-GB';

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();

        $firstFieldUs = clone $field;
        $firstFieldUs->id = 11;
        $firstFieldUs->fieldDefinitionId = 1;
        $firstFieldUs->languageCode = 'eng-US';

        $secondFieldGb = clone $field;
        $secondFieldGb->id = 22;
        $secondFieldGb->fieldDefinitionId = 2;
        $secondFieldGb->languageCode = 'eng-GB';

        $content->fields = [
            $firstFieldUs,
            $secondFieldGb,
        ];

        return $content;
    }

    /**
     * Returns a Content fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentNoFieldsFixture()
    {
        $content = new Content();
        $content->versionInfo = new VersionInfo();
        $content->versionInfo->versionNo = 1;
        $content->versionInfo->languageCodes = ['eng-US', 'eng-GB'];
        $content->versionInfo->contentInfo = new ContentInfo();
        $content->versionInfo->contentInfo->id = 42;
        $content->versionInfo->contentInfo->contentTypeId = 1;
        $content->versionInfo->contentInfo->mainLanguageCode = 'eng-GB';
        $content->fields = [];

        return $content;
    }

    /**
     * Returns a Content fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentSingleLanguageFixture()
    {
        $content = new Content();
        $content->versionInfo = new VersionInfo();
        $content->versionInfo->versionNo = 1;
        $content->versionInfo->languageCodes = ['eng-GB'];
        $content->versionInfo->contentInfo = new ContentInfo();
        $content->versionInfo->contentInfo->id = 42;
        $content->versionInfo->contentInfo->contentTypeId = 1;
        $content->versionInfo->contentInfo->mainLanguageCode = 'eng-GB';

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();
        $field->languageCode = 'eng-GB';

        $firstField = clone $field;
        $firstField->fieldDefinitionId = 1;

        $secondField = clone $field;
        $secondField->fieldDefinitionId = 2;

        $thirdField = clone $field;
        $thirdField->fieldDefinitionId = 3;

        $content->fields = [
            $firstField,
            $secondField,
            $thirdField,
        ];

        return $content;
    }

    /**
     * Returns a Content fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixture()
    {
        $content = $this->getContentPartialFieldsFixture();

        $field = new Field();
        $field->type = 'some-type';
        $field->value = new FieldValue();

        $firstFieldGb = clone $field;
        $firstFieldGb->id = 12;
        $firstFieldGb->fieldDefinitionId = 1;
        $firstFieldGb->languageCode = 'eng-GB';

        $secondFieldUs = clone $field;
        $secondFieldUs->id = 21;
        $secondFieldUs->fieldDefinitionId = 2;
        $secondFieldUs->languageCode = 'eng-US';

        $thirdFieldGb = clone $field;
        $thirdFieldGb->id = 32;
        $thirdFieldGb->fieldDefinitionId = 3;
        $thirdFieldGb->languageCode = 'eng-GB';

        $thirdFieldUs = clone $field;
        $thirdFieldUs->id = 31;
        $thirdFieldUs->fieldDefinitionId = 3;
        $thirdFieldUs->languageCode = 'eng-US';

        $content->fields = [
            $content->fields[0],
            $firstFieldGb,
            $secondFieldUs,
            $content->fields[1],
            $thirdFieldUs,
            $thirdFieldGb,
        ];

        return $content;
    }

    /**
     * Returns a ContentType fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function getContentTypeFixture()
    {
        $contentType = new Type();
        $firstFieldDefinition = new FieldDefinition(
            [
                'id' => 1,
                'fieldType' => 'some-type',
                'isTranslatable' => true,
            ]
        );
        $secondFieldDefinition = new FieldDefinition(
            [
                'id' => 2,
                'fieldType' => 'some-type',
                'isTranslatable' => false,
            ]
        );
        $thirdFieldDefinition = new FieldDefinition(
            [
                'id' => 3,
                'fieldType' => 'some-type',
                'isTranslatable' => false,
            ]
        );
        $contentType->fieldDefinitions = [
            $firstFieldDefinition,
            $secondFieldDefinition,
            $thirdFieldDefinition,
        ];

        return $contentType;
    }

    /**
     * Returns an UpdateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UpdateStruct
     */
    protected function getUpdateStructFixture()
    {
        $struct = new UpdateStruct();

        // Language with id=2 is eng-US
        $struct->initialLanguageId = 2;

        $content = $this->getContentFixture();

        foreach ($content->fields as $field) {
            // Skip untranslatable fields not in main language
            if (($field->fieldDefinitionId == 2 || $field->fieldDefinitionId == 3) && $field->languageCode != 'eng-GB') {
                continue;
            }
            $struct->fields[] = $field;
        }

        return $struct;
    }

    /**
     * Returns a FieldHandler to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandler()
    {
        $mock = new FieldHandler(
            $this->getContentGatewayMock(),
            $this->getMapperMock(),
            $this->getStorageHandlerMock(),
            $this->getLanguageHandler(),
            $this->getFieldTypeRegistryMock()
        );

        return $mock;
    }

    /**
     * Returns a StorageHandler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getStorageHandlerMock()
    {
        if (!isset($this->storageHandlerMock)) {
            $this->storageHandlerMock = $this->createMock(StorageHandler::class);
        }

        return $this->storageHandlerMock;
    }

    /**
     * Returns a Mapper mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMapperMock()
    {
        if (!isset($this->mapperMock)) {
            $this->mapperMock = $this->createMock(Mapper::class);
        }

        return $this->mapperMock;
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getContentGatewayMock()
    {
        if (!isset($this->contentGatewayMock)) {
            $this->contentGatewayMock = $this->getMockForAbstractClass(Gateway::class);
        }

        return $this->contentGatewayMock;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\FieldTypeRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldTypeRegistryMock()
    {
        if (!isset($this->fieldTypeRegistryMock)) {
            $this->fieldTypeRegistryMock = $this->createMock(FieldTypeRegistry::class);

            $this->fieldTypeRegistryMock->expects(
                $this->any()
            )->method(
                'getFieldType'
            )->with(
                $this->isType('string')
            )->will(
                $this->returnValue($this->getFieldTypeMock())
            );
        }

        return $this->fieldTypeRegistryMock;
    }

    /**
     * @return \eZ\Publish\SPI\FieldType\FieldType|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldTypeMock()
    {
        if (!isset($this->fieldTypeMock)) {
            $this->fieldTypeMock = $this->createMock(FieldType::class);
        }

        return $this->fieldTypeMock;
    }
}
