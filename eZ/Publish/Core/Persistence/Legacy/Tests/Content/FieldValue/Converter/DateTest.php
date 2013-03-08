<?php
/**
 * File containing the DateTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Date\Type as DateType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date as DateConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit_Framework_TestCase;
use DateTime;

/**
 * Test case for Date converter in Legacy storage
 *
 * @group fieldType
 * @group date
 */
class DateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date
     */
    protected $converter;

    /**
     * @var \DateTime
     */
    protected $date;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new DateConverter;
        $this->date = new DateTime( "@1362614400" );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = array(
            "timestamp" => $this->date->getTimestamp(),
            "rfc850"    => $this->date->format( \DateTime::RFC850  ),
        );
        $value->sortKey = $this->date->getTimestamp();
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $value->data["timestamp"], $storageFieldValue->dataInt );
        self::assertSame( $value->sortKey, $storageFieldValue->sortKeyInt );
        self::assertSame( "", $storageFieldValue->sortKeyString );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataInt = $this->date->getTimestamp();
        $storageFieldValue->sortKeyString = "";
        $storageFieldValue->sortKeyInt = $this->date->getTimestamp();
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame(
            array(
                "timestamp" => $this->date->getTimestamp(),
                "rfc850"    => null,
            ),
            $fieldValue->data
        );
        self::assertSame( $storageFieldValue->dataInt, $fieldValue->data["timestamp"] );
        self::assertSame( $storageFieldValue->sortKeyInt, $fieldValue->sortKey );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultEmpty()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "defaultType" => DateType::DEFAULT_EMPTY
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                "fieldTypeConstraints" => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            DateType::DEFAULT_EMPTY,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionDefaultCurrentDate()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "defaultType" => DateType::DEFAULT_CURRENT_DATE
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                "fieldTypeConstraints" => $fieldTypeConstraints,
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            DateType::DEFAULT_CURRENT_DATE,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultEmpty()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                "dataInt1" => DateType::DEFAULT_EMPTY
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertNull( $fieldDef->defaultValue->data );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Date::toFieldDefinition
     */
    public function testToFieldDefinitionDefaultCurrentDate()
    {
        $dateTime = new DateTime();
        $timestamp = $dateTime->setTime( 0, 0, 0 )->getTimestamp();
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                "dataInt1" => DateType::DEFAULT_CURRENT_DATE
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertInternalType( "array", $fieldDef->defaultValue->data );
        self::assertCount( 2, $fieldDef->defaultValue->data );
        self::assertNull( $fieldDef->defaultValue->data["rfc850"] );
        self::assertSame( $timestamp, $fieldDef->defaultValue->data["timestamp"] );
    }
}
