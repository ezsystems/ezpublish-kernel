<?php
/**
 * File containing the TimeTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Time\Type as TimeType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time as TimeConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
 * Test case for Time converter in Legacy storage
 *
 * @group fieldType
 * @group time
 */
class TimeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time
     */
    protected $converter;

    /**
     * @var int
     */
    protected $time;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new TimeConverter;
        $this->time = 3661;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = $this->time;
        $value->sortKey = $this->time;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $value->data, $storageFieldValue->dataInt );
        self::assertSame( $value->sortKey, $storageFieldValue->sortKeyInt );
        self::assertSame( "", $storageFieldValue->sortKeyString );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataInt = $this->time;
        $storageFieldValue->sortKeyString = "";
        $storageFieldValue->sortKeyInt = $this->time;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( $this->time, $fieldValue->data );
        self::assertSame( $storageFieldValue->dataInt, $fieldValue->data );
        self::assertSame( $storageFieldValue->sortKeyInt, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultEmpty()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "useSeconds" => true,
                "defaultType" => TimeType::DEFAULT_EMPTY
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                "fieldTypeConstraints" => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame( TimeType::DEFAULT_EMPTY, $storageFieldDef->dataInt1 );
        self::assertSame( 1, $storageFieldDef->dataInt2 );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultCurrentTime()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "useSeconds" => false,
                "defaultType" => TimeType::DEFAULT_CURRENT_TIME
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                "fieldTypeConstraints" => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame( TimeType::DEFAULT_CURRENT_TIME, $storageFieldDef->dataInt1 );
        self::assertSame( 0, $storageFieldDef->dataInt2 );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultEmpty()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                "dataInt2" => 1,
                "dataInt1" => TimeType::DEFAULT_EMPTY
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertNull( $fieldDef->defaultValue->data );
        self::assertEquals(
            new FieldSettings(
                array(
                    "useSeconds" => true,
                    "defaultType" => TimeType::DEFAULT_EMPTY
                )
            ),
            $fieldDef->fieldTypeConstraints->fieldSettings
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Time::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultCurrentTime()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                "dataInt2" => 0,
                "dataInt1" => TimeType::DEFAULT_CURRENT_TIME
            )
        );

        $dateTime = new DateTime();
        $dateTime->setTime( 0, 0, 0 );
        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertSame( time() - $dateTime->getTimestamp(), $fieldDef->defaultValue->data );
        self::assertEquals(
            new FieldSettings(
                array(
                    "useSeconds" => false,
                    "defaultType" => TimeType::DEFAULT_CURRENT_TIME
                )
            ),
            $fieldDef->fieldTypeConstraints->fieldSettings
        );
    }
}
