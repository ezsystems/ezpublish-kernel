<?php

/**
 * File containing the FieldType interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence;

use eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * The field type interface which field types available to storage engines have to implement.
 *
 * @see \eZ\Publish\SPI\FieldType\FieldType
 */
interface FieldType
{
    /**
     * Returns the empty value for the field type that can be processed by the storage engine.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getEmptyValue();

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     *
     * @return bool
     */
    public function isEmptyValue(FieldValue $value);
}
