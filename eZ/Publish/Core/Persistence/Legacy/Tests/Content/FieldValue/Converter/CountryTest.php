<?php
/**
 * File containing the CountryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;
use eZ\Publish\Core\FieldType\Country\Type as CountryType,
    eZ\Publish\Core\FieldType\FieldSettings,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country as CountryConverter,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Country converter in Legacy storage
 */
class CountryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\FieldType\Country\Type
     */
    protected $type;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->type = new CountryType(
            array(
                "BE" => array(
                    "Name" => "Belgium",
                    "Alpha2" => "BE",
                    "Alpha3" => "BEL",
                    "IDC" => 32,
                ),
                "FR" => array(
                    "Name" => "France",
                    "Alpha2" => "FR",
                    "Alpha3" => "FRA",
                    "IDC" => 33,
                ),
            )
        );
        $this->converter = new CountryConverter();
    }

    /**
     * @group fieldType
     * @group country
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = "BE,FR";
        $value->sortKey = "belgium,france";
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( "BE,FR", $storageFieldValue->dataText );
        self::assertSame( "belgium,france", $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = "BE,FR";
        $storageFieldValue->sortKeyString = "belgium,france";
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( "BE,FR", $fieldValue->data );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionMultiple()
    {
        $defaultValue = new FieldValue;
        $defaultValue->data = "BE,FR";
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => true
            )
        );

        $storageFieldDef = new StorageFieldDefinition;
        $this->converter->toStorageFieldDefinition(
            new PersistenceFieldDefinition(
                array(
                    "fieldTypeConstraints" => $fieldTypeConstraints,
                    "defaultValue" => $defaultValue,
                )
            ),
            $storageFieldDef
        );
        self::assertSame(
            1,
            $storageFieldDef->dataInt1
        );
        self::assertSame(
            "BE,FR",
            $storageFieldDef->dataText5
        );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionSingle()
    {
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => false,
            )
        );

        $storageFieldDef = new StorageFieldDefinition;
        $this->converter->toStorageFieldDefinition(
            new PersistenceFieldDefinition(
                array(
                    "fieldTypeConstraints" => $fieldTypeConstraints,
                )
            ),
            $storageFieldDef
        );
        self::assertSame(
            0,
            $storageFieldDef->dataInt1
        );
        self::assertEmpty(
            $storageFieldDef->dataText5
        );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toFieldDefinition
     */
    public function testToFieldDefinitionMultiple()
    {
        $fieldDef = new PersistenceFieldDefinition;

        $this->converter->toFieldDefinition(
            new StorageFieldDefinition(
                array(
                    "dataInt1" => 1,
                    "dataText5" => 'BE,FR',
                )
            ),
            $fieldDef
        );
        self::assertInstanceOf( "eZ\\Publish\\Core\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertTrue(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
        self::assertEquals(
            $this->type->fromHash( array( "BE", "FR" ) ),
            $fieldDef->defaultValue->data
        );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toFieldDefinition
     */
    public function testToFieldDefinitionSingle()
    {
        $fieldDef = new PersistenceFieldDefinition;

        $this->converter->toFieldDefinition(
            new StorageFieldDefinition(
                array(
                    "dataInt1" => 0,
                    "dataText5" => ''
                )
            ),
            $fieldDef
        );
        self::assertInstanceOf( "eZ\\Publish\\Core\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertFalse(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
        self::assertNull(
            $fieldDef->defaultValue->data
        );
    }
}
