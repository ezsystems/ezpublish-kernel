<?php

/**
 * File containing the Value interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\FieldType;

/**
 * Interface for field value classes.
 */
interface Value
{
    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString();
}
