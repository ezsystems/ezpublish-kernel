<?php

/**
 * File containing the MediaTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\Media\Type as MediaType;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\MediaConverter;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit\Framework\TestCase;

/**
 * Test case for MediaType converter in Legacy storage.
 */
class MediaTest extends TestCase
{
    protected $converter;

    protected function setUp()
    {
        $this->converter = new MediaConverter();
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\MediaConverter::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $storageFieldDef = new StorageFieldDefinition();

        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->validators = [
            // Setting max file size to 1MB (1.048.576 bytes)
            'FileSizeValidator' => ['maxFileSize' => 1048576],
        ];
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'mediaType' => MediaType::TYPE_HTML5_VIDEO,
            ]
        );

        $fieldDef = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => $fieldTypeConstraints,
                'defaultValue' => null,
            ]
        );

        $this->converter->toStorageFieldDefinition($fieldDef, $storageFieldDef);

        self::assertSame(
            $fieldDef->fieldTypeConstraints->validators['FileSizeValidator'],
            ['maxFileSize' => $storageFieldDef->dataInt1]
        );
        self::assertSame(
            $fieldDef->fieldTypeConstraints->fieldSettings['mediaType'],
            $storageFieldDef->dataText1
        );
    }

    /**
     * @group fieldType
     * @group ezmedia
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\MediaConverter::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $fieldDef = new PersistenceFieldDefinition();
        $storageDef = new StorageFieldDefinition(
            [
                'dataInt1' => 1048576,
                'dataText1' => MediaType::TYPE_HTML5_VIDEO,
            ]
        );

        $this->converter->toFieldDefinition($storageDef, $fieldDef);
        self::assertSame(
            [
                'FileSizeValidator' => ['maxFileSize' => $storageDef->dataInt1],
            ],
            $fieldDef->fieldTypeConstraints->validators
        );
        self::assertInstanceOf('eZ\\Publish\\Core\\FieldType\\FieldSettings', $fieldDef->fieldTypeConstraints->fieldSettings);
        self::assertSame(
            ['mediaType' => MediaType::TYPE_HTML5_VIDEO],
            $fieldDef->fieldTypeConstraints->fieldSettings->getArrayCopy()
        );
    }
}
