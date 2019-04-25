<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter as ConverterInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use Symfony\Component\Serializer\Serializer;

class Converter implements ConverterInterface
{
    /** @var \Symfony\Component\Serializer\Serializer */
    private $serializer;

    /** @var string|null */
    private $settingsClass;

    public function __construct(Serializer $serializer, ?string $settingsClass = null)
    {
        $this->serializer = $serializer;
        $this->settingsClass = $settingsClass;
    }

    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue): void
    {
        $data = $value->data;
        if ($data !== null) {
            $data = $this->serializer->encode($data, 'json');
        }

        $storageFieldValue->dataText = $data;
    }

    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue): void
    {
        $data = $value->dataText;
        if ($data !== null) {
            $data = $this->serializer->decode($data, 'json');
        }

        $fieldValue->data = $data;
    }

    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef): void
    {
        if ($this->settingsClass === null) {
            return;
        }

        $settings = $fieldDef->fieldTypeConstraints->fieldSettings;
        if ($settings !== null) {
            $settings = $this->serializer->serialize($settings['settings'], 'json');
        }

        $storageDef->dataText5 = $settings;
    }

    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef): void
    {
        if ($this->settingsClass === null) {
            return;
        }

        $settings = $storageDef->dataText5;
        if ($settings !== null) {
            $settings = new FieldSettings([
                'settings' => $this->serializer->deserialize($settings, $this->settingsClass, 'json'),
            ]);
        }

        $fieldDef->fieldTypeConstraints->fieldSettings = $settings;
    }

    public function getIndexColumn()
    {
        return false;
    }
}
