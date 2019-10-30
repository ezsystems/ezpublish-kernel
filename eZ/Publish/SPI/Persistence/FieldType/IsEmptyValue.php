<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\FieldType;

use eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * The field type interface which field types available to storage engines have to implement.
 *
 * @see \eZ\Publish\SPI\FieldType\FieldType
 *
 * @deprecated since 7.5.6. In 8.0 (for eZ Platform 3.0) it will be merged into the
 *             `\eZ\Publish\SPI\Persistence\FieldType` interface
 */
interface IsEmptyValue
{
    /**
     * Returns the empty value for the field type that can be processed by the storage engine.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function isEmptyValue(FieldValue $fieldValue): bool;
}
