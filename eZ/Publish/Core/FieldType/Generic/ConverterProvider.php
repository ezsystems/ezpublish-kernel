<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface;
use eZ\Publish\Core\FieldType\Generic\Type as GenericType;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

class ConverterProvider implements ConverterRegistryInterface
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface */
    private $innerRegistry;

    /** @var \eZ\Publish\Core\Persistence\FieldTypeRegistry */
    private $fieldTypeRegistry;

    /** @var \eZ\Publish\Core\FieldType\Generic\ConverterFactory */
    private $converterFactory;

    public function __construct(
        ConverterRegistryInterface $innerRegistry,
        FieldTypeRegistry $fieldTypeRegistry,
        ConverterFactory $converterFactory)
    {
        $this->innerRegistry = $innerRegistry;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->converterFactory = $converterFactory;
    }

    public function register($typeName, $converter): void
    {
        $this->innerRegistry->register($typeName, $converter);
    }

    public function hasConverter(string $typeName): bool
    {
        return $this->innerRegistry->hasConverter($typeName);
    }

    public function getConverter($typeName): Converter
    {
        if ($this->hasConverter($typeName)) {
            return $this->innerRegistry->getConverter($typeName);
        }

        $fieldType = $this->fieldTypeRegistry->getCoreFieldType($typeName);
        if ($this->isGenericFieldType($typeName)) {
            /** @var \eZ\Publish\Core\FieldType\Generic\Type $fieldType */
            $converter = $this->converterFactory->createForFieldType(
                $fieldType->getSettingsClass()
            );

            // Register and return default converter
            $this->innerRegistry->register($typeName, $converter);

            return $converter;
        }

        throw new NotFound($typeName);
    }

    private function isGenericFieldType(string $identifier): bool
    {
        return $this->fieldTypeRegistry->getCoreFieldType($identifier) instanceof GenericType;
    }
}
