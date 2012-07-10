<?php
/**
 * File containing the TextBlockTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;
use eZ\Publish\Core\FieldType\TextBlock\Value as TextBlockValue,
    eZ\Publish\Core\FieldType\FieldSettings,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlock as TextBlockConverter,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints,
    PHPUnit_Framework_TestCase;

/**
 * Test case for TextBlock converter in Legacy storage
 */
class TextBlockTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlock
     */
    protected $converter;

    protected $longText;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new TextBlockConverter;
        $this->longText = <<<EOT
Now that we know who you are, I know who I am.
I'm not a mistake! It all makes sense! In a comic, you know how you can tell who the arch-villain's going to be?
He's the exact opposite of the hero. And most times they're friends, like you and me! I should've known way back when...
You know why, David? Because of the kids.

They called me Mr Glass.
EOT;
    }

    /**
     * @group fieldType
     * @group textBlock
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlock::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = $this->longText;
        $value->sortKey = array( 'sort_key_string' => 'Now that we know who you are' );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $value->data, $storageFieldValue->dataText );
        self::assertSame( $value->sortKey['sort_key_string'], $storageFieldValue->sortKeyString );
        self::assertSame( 0, $storageFieldValue->sortKeyInt );
    }

    /**
     * @group fieldType
     * @group textBlock
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlock::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = $this->longText;
        $storageFieldValue->sortKeyString = 'Now that we know who you are';
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertSame( $storageFieldValue->dataText, $fieldValue->data );
        self::assertSame( $storageFieldValue->sortKeyString, $fieldValue->sortKey['sort_key_string'] );
    }

    /**
     * @group fieldType
     * @group textBlock
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlock::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $fieldTypeConstraints = new FieldTypeConstraints;
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "textRows" => 15
            )
        );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => new TextBlockValue
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            15,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @group fieldType
     * @group textBlock
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlock::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => 20
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertNull( $fieldDef->fieldTypeConstraints->validators );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\Repository\\FieldType\\FieldSettings', $fieldDef->fieldTypeConstraints->fieldSettings );
        self::assertSame(
            array( "textRows" => 20 ),
            $fieldDef->fieldTypeConstraints->fieldSettings->getArrayCopy()
        );
    }
}
