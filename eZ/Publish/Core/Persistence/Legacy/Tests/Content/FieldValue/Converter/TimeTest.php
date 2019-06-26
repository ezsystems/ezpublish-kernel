<?php

/**
 * File containing the TimeTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Time\Type as TimeType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Test case for Time converter in Legacy storage.
 *
 * @group fieldType
 * @group time
 */
class TimeTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter */
    protected $converter;

    /** @var int */
    protected $time;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new TimeConverter();
        $this->time = 3661;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = $this->time;
        $value->sortKey = $this->time;
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        self::assertSame($value->data, $storageFieldValue->dataInt);
        self::assertSame($value->sortKey, $storageFieldValue->sortKeyInt);
        self::assertSame('', $storageFieldValue->sortKeyString);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataInt = $this->time;
        $storageFieldValue->sortKeyString = '';
        $storageFieldValue->sortKeyInt = $this->time;
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertSame($this->time, $fieldValue->data);
        self::assertSame($storageFieldValue->dataInt, $fieldValue->data);
        self::assertSame($storageFieldValue->sortKeyInt, $fieldValue->sortKey);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultEmpty()
    {
        $storageFieldDef = new StorageFieldDefinition();
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'useSeconds' => true,
                'defaultType' => TimeType::DEFAULT_EMPTY,
            ]
        );
        $fieldDef = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => $fieldTypeConstraints,
            ]
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);
        self::assertSame(TimeType::DEFAULT_EMPTY, $storageFieldDef->dataInt1);
        self::assertSame(1, $storageFieldDef->dataInt2);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultCurrentTime()
    {
        $storageFieldDef = new StorageFieldDefinition();
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'useSeconds' => false,
                'defaultType' => TimeType::DEFAULT_CURRENT_TIME,
            ]
        );
        $fieldDef = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => $fieldTypeConstraints,
            ]
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);
        self::assertSame(TimeType::DEFAULT_CURRENT_TIME, $storageFieldDef->dataInt1);
        self::assertSame(0, $storageFieldDef->dataInt2);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultEmpty()
    {
        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            [
                'dataInt2' => 1,
                'dataInt1' => TimeType::DEFAULT_EMPTY,
            ]
        );

        $this->converter->toFieldDefinition($storageDef, $fieldDef);
        self::assertNull($fieldDef->defaultValue->data);
        self::assertEquals(
            new FieldSettings(
                [
                    'useSeconds' => true,
                    'defaultType' => TimeType::DEFAULT_EMPTY,
                ]
            ),
            $fieldDef->fieldTypeConstraints->fieldSettings
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TimeConverter::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultCurrentTime()
    {
        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            [
                'dataInt2' => 0,
                'dataInt1' => TimeType::DEFAULT_CURRENT_TIME,
            ]
        );

        $dateTime = new DateTime();
        $dateTime->setTime(0, 0, 0);
        $this->converter->toFieldDefinition($storageDef, $fieldDef);
        self::assertSame(time() - $dateTime->getTimestamp(), $fieldDef->defaultValue->data);
        self::assertEquals(
            new FieldSettings(
                [
                    'useSeconds' => false,
                    'defaultType' => TimeType::DEFAULT_CURRENT_TIME,
                ]
            ),
            $fieldDef->fieldTypeConstraints->fieldSettings
        );
    }
}
