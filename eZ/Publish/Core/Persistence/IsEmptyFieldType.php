<?php

/**
 * File containing the eZ\Publish\Core\Persistence\FieldType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\IsEmptyFieldType as IsEmptyFieldTypeInterface;

/**
 * This class represents a FieldType available to SPI users.
 *
 * @see \eZ\Publish\SPI\FieldType\FieldType
 */
class IsEmptyFieldType extends FieldType implements IsEmptyFieldTypeInterface
{
    /**
     * Returns the empty value for the field type that can be processed by the storage engine.
     */
    public function isEmptyValue(FieldValue $fieldValue): bool
    {
        return $this->internalFieldType->isEmptyValue(
            $this->internalFieldType->fromPersistenceValue($fieldValue)
        );
    }
}
