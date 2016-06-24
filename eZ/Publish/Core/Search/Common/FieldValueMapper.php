<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw field values to something search engine can understand.
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
