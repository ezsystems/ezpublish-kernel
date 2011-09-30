<?php
/**
 * File containing the SelectionConverterLegacy class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Selection\Value as SelectionValue,
    ezp\Content\FieldType\FieldSettings,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection as SelectionConverter,
    ezp\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    ezp\Persistence\Content\FieldTypeConstraints,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Selection converter in Legacy storage
 */
class SelectionConverterLegacyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new SelectionConverter;
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = new SelectionValue( array( "Choice1", "Choice2" ) );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        // @todo Have some assert here?
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = "0-1";
        $storageFieldValue->sortKeyString = "0-1";
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\Selection\\Value", $fieldValue->data );
        // @todo Have some additional assert here?
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionMultiple()
    {
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
                )
            ),
            $storageFieldDef
        );
        self::assertSame(
            1,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionSingle()
    {
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => false
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
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection::toFieldDefinition
     */
    public function testToFieldDefinitionMultiple()
    {
        $fieldDef = new PersistenceFieldDefinition;

        $this->converter->toFieldDefinition(
            new StorageFieldDefinition(
                array(
                    "dataInt1" => 1,
                )
            ),
            $fieldDef
        );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertTrue(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
    }

    /**
     * @group fieldType
     * @group selection
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Selection::toFieldDefinition
     */
    public function testToFieldDefinitionSingle()
    {
        $fieldDef = new PersistenceFieldDefinition;

        $this->converter->toFieldDefinition(
            new StorageFieldDefinition(
                array(
                    "dataInt1" => 0,
                )
            ),
            $fieldDef
        );
        self::assertInstanceOf( "ezp\\Content\\FieldType\\FieldSettings", $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertFalse(
            $fieldDef->fieldTypeConstraints->fieldSettings["isMultiple"]
        );
    }
}
