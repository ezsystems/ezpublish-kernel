<?php
/**
 * File containing the CountryTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\FieldValue\Converter;
use ezp\Content\FieldType\Country\Value as CountryValue,
    ezp\Content\FieldType\FieldSettings,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country as CountryConverter,
    ezp\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    ezp\Persistence\Content\FieldTypeConstraints,
    PHPUnit_Framework_TestCase,
    DOMDocument,
    DOMXPath;

/**
 * Test case for Country converter in Legacy storage
 */
class CountryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new CountryConverter;
    }

    /**
     * @group fieldType
     * @group country
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = new CountryValue( array( "BE", "FR" ) );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( "BE,FR", $storageFieldValue->dataText );
        self::assertSame( "belgium,france", $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country::toFieldValue
     * @covers \ezp\Content\FieldType\Country\Value::getCountriesInfo
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = "BE,FR";
        $storageFieldValue->sortKeyString = "belgium,france";
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Country\\Value", $fieldValue->data );
        self::assertSame(
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
            ),
            $fieldValue->data->getCountriesInfo()
        );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionMultiple()
    {
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => true,
                "default" => array(
                    "Belgium",
                    "France",
                ),
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
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country::toStorageFieldDefinition
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
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country::toFieldDefinition
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
        self::assertInstanceOf( "ezp\\Content\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertTrue(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
        self::assertEquals(
            new CountryValue( array( "BE", "FR" ) ),
            $fieldDef->fieldTypeConstraints->fieldSettings["default"]
        );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Country::toFieldDefinition
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
        self::assertInstanceOf( "ezp\\Content\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertFalse(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
        self::assertNull(
            $fieldDef->fieldTypeConstraints->fieldSettings["default"]
        );
    }
}
