<?php
/**
 * File containing the TextLineTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;
use ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Content\FieldType\FieldSettings,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine as TextLineConverter,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    PHPUnit_Framework_TestCase;

/**
 * Test case for TextLine converter in Legacy storage
 */
class TextLineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new TextLineConverter;
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = new TextLineValue( "He's holding a thermal detonator!" );
        $value->sortKey = array( 'sort_key_string' => "He's holding" );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $value->data->text, $storageFieldValue->dataText );
        self::assertSame( $value->sortKey['sort_key_string'], $storageFieldValue->sortKeyString );
        self::assertSame( 0, $storageFieldValue->sortKeyInt );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = 'When 900 years old, you reach... Look as good, you will not.';
        $storageFieldValue->sortKeyString = 'When 900 years old, you reach...';
        $storageFieldValue->sortKeyInt = 0;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\TextLine\\Value', $fieldValue->data );
        self::assertSame( $storageFieldValue->dataText, $fieldValue->data->text );
        self::assertSame( $storageFieldValue->sortKeyString, $fieldValue->sortKey['sort_key_string'] );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionWithValidator()
    {
        $defaultText = 'This is a default text';
        $storageFieldDef = new StorageFieldDefinition;
        $defaultValue = new FieldValue;
        $defaultValue->data = new TextLineValue( $defaultText );
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->validators = array(
            TextLineConverter::STRING_LENGTH_VALIDATOR_FQN => array( 'maxStringLength' => 100 )
        );
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultText' => $defaultText
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
            $fieldDef->fieldTypeConstraints->validators[TextLineConverter::STRING_LENGTH_VALIDATOR_FQN],
            array( 'maxStringLength' => $storageFieldDef->dataInt1 )
        );
        self::assertSame(
            $fieldDef->fieldTypeConstraints->fieldSettings['defaultText'],
            $storageFieldDef->dataText1
        );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionNoValidator()
    {
        $defaultText = 'This is a default text';
        $storageFieldDef = new StorageFieldDefinition;
        $defaultValue = new FieldValue;
        $defaultValue->data = new TextLineValue( $defaultText );
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => $defaultValue
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            0,
            $storageFieldDef->dataInt1
        );
        self::assertSame(
            $fieldDef->defaultValue->data->text,
            $storageFieldDef->dataText1
        );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $defaultText = 'This is a default value';
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => 100,
                'dataText1' => $defaultText
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertSame(
            array(
                TextLineConverter::STRING_LENGTH_VALIDATOR_FQN => array( 'maxStringLength' => 100 )
            ),
            $fieldDef->fieldTypeConstraints->validators
        );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\TextLine\\Value', $fieldDef->defaultValue->data );
        self::assertSame( $defaultText, $fieldDef->defaultValue->data->text );
    }
}
