<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw field values to something search engine can understand.
 * This is used when indexing Content and matching Content fields.
 * Actual format of the returned value depends on the search engine
 * implementation, meaning engines should override common implementation
 * as needed, but the same input should be handled across engines.
 *
 * @see \eZ\Publish\SPI\Search\FieldType
 */
abstract class FieldValueMapper
{
    /**
     * Check if field can be mapped.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return bool
     */
    abstract public function canMap(Field $field);

    /**
     * Map field value to a proper search engine representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed|null Returns null on empty value
     */
    abstract public function map(Field $field);
}
