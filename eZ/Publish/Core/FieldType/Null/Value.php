<?php

/**
 * File containing the Null Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Null;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Null field type.
 */
class Value extends BaseValue
{
    /**
     * Content of the value.
     *
     * @var mixed
     */
    public $value = null;

    /**
     * Construct a new Value object and initialize with $value.
     *
     * @param int $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
