<?php

/**
 * File containing the Integer Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Integer;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Integer field type.
 */
class Value extends BaseValue
{
    /**
     * Content of the value.
     *
     * @var int
     */
    public $value;

    /**
     * Construct a new Value object and initialize with $value.
     *
     * @param int|null $value
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
