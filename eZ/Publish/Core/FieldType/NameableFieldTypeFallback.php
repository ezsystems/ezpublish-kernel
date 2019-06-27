<?php

/**
 * File containing the NameableFieldTypeFallback class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value as SPIValue;

/**
 * Class NameableFieldTypeFallback.
 *
 * Provides fallback to for all FieldTypes that don't have a Nameable FieldType service, by
 * falling back to using the older, but limited FieldType->getName() method.
 */
class NameableFieldTypeFallback implements Nameable
{
    /** @var \eZ\Publish\SPI\FieldType\FieldType */
    private $fieldType;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     */
    public function __construct(SPIFieldType $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * @param \eZ\Publish\SPI\FieldType\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(SPIValue $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        return $this->fieldType->getName($value);
    }
}
