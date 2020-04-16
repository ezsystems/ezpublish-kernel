<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\FieldType as FieldTypeInterface;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;

/**
 * This class represents a FieldType available to SPI users.
 *
 * @see \eZ\Publish\SPI\FieldType\FieldType
 */
class FieldType implements FieldTypeInterface, FieldTypeInterface\IsEmptyValue
{
    /**
     * Holds internal FieldType object.
     *
     * @var \eZ\Publish\SPI\FieldType\FieldType
     */
    protected $internalFieldType;

    /**
     * Creates a new FieldType object.
     *
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     */
    public function __construct(SPIFieldType $fieldType)
    {
        $this->internalFieldType = $fieldType;
    }

    /**
     * Returns the empty value for the field type that can be processed by the storage engine.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getEmptyValue()
    {
        return $this->internalFieldType->toPersistenceValue(
            $this->internalFieldType->getEmptyValue()
        );
    }

    public function isEmptyValue(FieldValue $fieldValue): bool
    {
        return $this->internalFieldType->isEmptyValue(
            $this->internalFieldType->fromPersistenceValue($fieldValue)
        );
    }
}
