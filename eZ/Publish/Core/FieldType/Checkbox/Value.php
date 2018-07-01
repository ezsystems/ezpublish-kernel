<?php

/**
 * File containing the Checkbox Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Checkbox;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Checkbox field type.
 */
class Value extends BaseValue
{
    /**
     * Boolean value.
     *
     * @var bool
     */
    public $bool;

    /**
     * Construct a new Value object and initialize it $boolValue.
     *
     * @param bool $boolValue
     */
    public function __construct($boolValue = false)
    {
        $this->bool = $boolValue;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     *
     * @return string
     */
    public function __toString()
    {
        return $this->bool ? '1' : '0';
    }
}
