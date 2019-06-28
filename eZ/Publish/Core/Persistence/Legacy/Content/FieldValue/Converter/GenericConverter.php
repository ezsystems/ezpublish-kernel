<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\FieldType\Generic\ValueSerializerInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter as ConverterInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

class GenericConverter implements ConverterInterface
{
    /** @var \eZ\Publish\Core\FieldType\Generic\ValueSerializerInterface */
    private $serializer;

    public function __construct(ValueSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue): void
    {
        $data = $value->data;
        if ($data !== null) {
            $data = $this->serializer->encode($data);
        }

        $storageFieldValue->dataText = $data;
        $storageFieldValue->sortKeyString = (string)$value->sortKey;
    }

    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue): void
    {
        $data = $value->dataText;
        if ($data !== null) {
            $data = $this->serializer->decode($data);
        }

        $fieldValue->data = $data;
        $fieldValue->sortKey = $value->sortKeyString;
    }

    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef): void
    {
        $settings = $fieldDef->fieldTypeConstraints->fieldSettings;
        if ($settings !== null) {
            $settings = $this->serializer->encode($settings);
        }

        $storageDef->dataText5 = $settings;
    }

    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef): void
    {
        $settings = $storageDef->dataText5;
        if ($settings !== null) {
            $settings = new FieldSettings($this->serializer->decode($settings));
        }

        $fieldDef->fieldTypeConstraints->fieldSettings = $settings;
    }

    public function getIndexColumn(): string
    {
        return 'sort_key_string';
    }
}
