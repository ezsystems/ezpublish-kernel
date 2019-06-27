<?php

/**
 * File containing the TextBlockTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\TextBlock\Value as TextBlockValue;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlockConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit\Framework\TestCase;

/**
 * Test case for TextBlock converter in Legacy storage.
 */
class TextBlockTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlockConverter */
    protected $converter;

    protected $longText;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new TextBlockConverter();
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlockConverter::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = $this->longText;
        $value->sortKey = 'Now that we know who you are';
        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);
        self::assertSame($value->data, $storageFieldValue->dataText);
        self::assertSame($value->sortKey, $storageFieldValue->sortKeyString);
        self::assertSame(0, $storageFieldValue->sortKeyInt);
    }

    /**
     * @group fieldType
     * @group textBlock
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlockConverter::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = $this->longText;
        $storageFieldValue->sortKeyString = 'Now that we know who you are';
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
        self::assertSame($storageFieldValue->dataText, $fieldValue->data);
        self::assertSame($storageFieldValue->sortKeyString, $fieldValue->sortKey);
    }

    /**
     * @group fieldType
     * @group textBlock
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlockConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $storageFieldDef = new StorageFieldDefinition();
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'textRows' => 15,
            ]
        );
        $fieldDef = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => new TextBlockValue(),
            ]
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);
        self::assertSame(
            15,
            $storageFieldDef->dataInt1
        );
    }

    /**
     * @group fieldType
     * @group textBlock
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextBlockConverter::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            [
                'dataInt1' => 20,
            ]
        );

        $this->converter->toFieldDefinition($storageDef, $fieldDef);

        self::assertSame('', $fieldDef->defaultValue->sortKey);
        self::assertNull($fieldDef->fieldTypeConstraints->validators);
        self::assertInstanceOf('eZ\\Publish\\Core\\FieldType\\FieldSettings', $fieldDef->fieldTypeConstraints->fieldSettings);
        self::assertSame(
            ['textRows' => 20],
            $fieldDef->fieldTypeConstraints->fieldSettings->getArrayCopy()
        );
    }
}
