<?php
/**
 * File containing the CheckboxTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\FieldValue\Converter;
use ezp\Content\FieldType\Checkbox\Value as CheckboxValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Checkbox as CheckboxConverter,
    ezp\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Content\FieldType\FieldSettings,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Checkbox converter in Legacy storage
 */
class CheckboxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Checkbox
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new CheckboxConverter;
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Checkbox::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = new CheckboxValue( true );
        $value->sortKey = array( 'sort_key_int' => 1 );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( (int)$value->data->bool, $storageFieldValue->dataInt );
        self::assertSame( $value->sortKey['sort_key_int'], $storageFieldValue->sortKeyInt );
        self::assertSame( '', $storageFieldValue->sortKeyString );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Checkbox::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataInt = 1;
        $storageFieldValue->sortKeyInt = 1;
        $storageFieldValue->sortKeyString = '';
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\Checkbox\\Value', $fieldValue->data );
        self::assertSame( (bool)$storageFieldValue->dataInt, $fieldValue->data->bool );
        self::assertSame( $storageFieldValue->sortKeyInt, $fieldValue->sortKey['sort_key_int'] );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Checkbox::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $defaultBool = false;
        $storageFieldDef = new StorageFieldDefinition;
        $defaultValue = new FieldValue;
        $defaultValue->data = new CheckboxValue( $defaultBool );
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultValue' => $defaultBool
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => $defaultValue
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            (int)$fieldDef->fieldTypeConstraints->fieldSettings['defaultValue'],
            $storageFieldDef->dataInt3
        );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Checkbox::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $defaultBool = true;
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt3' => 1,
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\Checkbox\\Value', $fieldDef->defaultValue->data );
        self::assertSame( $defaultBool, $fieldDef->defaultValue->data->bool );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\FieldSettings', $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertSame(
            array( 'defaultValue' => $defaultBool ),
            $fieldDef->fieldTypeConstraints->fieldSettings->getArrayCopy()
        );
    }
}
