<?php

/**
 * File containing the DateTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Date\Type as DateType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Test case for Date converter in Legacy storage.
 *
 * @group fieldType
 * @group date
 */
class DateTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter */
    protected $converter;

    /** @var \DateTime */
    protected $date;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new DateConverter();
        $this->date = new DateTime('@1362614400');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = [
            'timestamp' => $this->date->getTimestamp(),
            'rfc850' => $this->date->format(\DateTime::RFC850),
        ];
        $value->sortKey = $this->date->getTimestamp();
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        self::assertSame($value->data['timestamp'], $storageFieldValue->dataInt);
        self::assertSame($value->sortKey, $storageFieldValue->sortKeyInt);
        self::assertSame('', $storageFieldValue->sortKeyString);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataInt = $this->date->getTimestamp();
        $storageFieldValue->sortKeyString = '';
        $storageFieldValue->sortKeyInt = $this->date->getTimestamp();
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertSame(
            [
                'timestamp' => $this->date->getTimestamp(),
                'rfc850' => null,
            ],
            $fieldValue->data
        );
        self::assertSame($storageFieldValue->dataInt, $fieldValue->data['timestamp']);
        self::assertSame($storageFieldValue->sortKeyInt, $fieldValue->sortKey);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultEmpty()
    {
        $storageFieldDef = new StorageFieldDefinition();
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'defaultType' => DateType::DEFAULT_EMPTY,
            ]
        );
        $fieldDef = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => $fieldTypeConstraints,
            ]
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);
        self::assertSame(
            DateType::DEFAULT_EMPTY,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultCurrentDate()
    {
        $storageFieldDef = new StorageFieldDefinition();
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'defaultType' => DateType::DEFAULT_CURRENT_DATE,
            ]
        );
        $fieldDef = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => $fieldTypeConstraints,
            ]
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);
        self::assertSame(
            DateType::DEFAULT_CURRENT_DATE,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultEmpty()
    {
        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            [
                'dataInt1' => DateType::DEFAULT_EMPTY,
            ]
        );

        $this->converter->toFieldDefinition($storageDef, $fieldDef);
        self::assertNull($fieldDef->defaultValue->data);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateConverter::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultCurrentDate()
    {
        $timestamp = time();
        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            [
                'dataInt1' => DateType::DEFAULT_CURRENT_DATE,
            ]
        );

        $this->converter->toFieldDefinition($storageDef, $fieldDef);
        self::assertInternalType('array', $fieldDef->defaultValue->data);
        self::assertCount(3, $fieldDef->defaultValue->data);
        self::assertNull($fieldDef->defaultValue->data['rfc850']);
        self::assertSame($timestamp, $fieldDef->defaultValue->data['timestamp']);
        self::assertSame('now', $fieldDef->defaultValue->data['timestring']);
    }
}
