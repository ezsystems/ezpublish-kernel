<?php
/**
 * File containing the CountryTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country as CountryConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Country converter in Legacy storage
 */
class CountryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new CountryConverter();
    }

    public function providerForTestToStorageValue()
    {
        return array(
            array( array( "BE", "FR" ), "belgium,france", "BE,FR", "belgium,france" ),
            array( null, "", "", "" ),
        );
    }

    /**
     * @group fieldType
     * @group country
     * @dataProvider providerForTestToStorageValue
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toStorageValue
     */
    public function testToStorageValue( $data, $sortKey, $dataText, $sortKeyString )
    {
        $value = new FieldValue;
        $value->data = $data;
        $value->sortKey = $sortKey;
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $dataText, $storageFieldValue->dataText );
        self::assertSame( $sortKeyString, $storageFieldValue->sortKeyString );
    }

    public function providerForTestToFieldValue()
    {
        return array(
            array( "BE,FR", "belgium,france", array( "BE", "FR" ) ),
            array( "", "", null ),
        );
    }

    /**
     * @group fieldType
     * @group country
     * @dataProvider providerForTestToFieldValue
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toFieldValue
     */
    public function testToFieldValue( $dataText, $sortKeyString, $data )
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = $dataText;
        $storageFieldValue->sortKeyString = $sortKeyString;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( $data, $fieldValue->data );
    }

    /**
     * @group fieldType
     * @group country
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Country::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionMultiple()
    {
        $defaultValue = new FieldValue;
        $defaultValue->data = array( "BE", "FR" );
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
            array( "BE", "FR" ),
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
