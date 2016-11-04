<?php

/**
 * File containing the Rating Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Rating;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Rating field type.
 */
class Value extends BaseValue
{
    /**
     * Is rating disabled.
     *
     * @var bool
     */
    public $isDisabled = false;

    /**
     * Construct a new Value object and initialize it with its $isDisabled state.
     *
     * @param bool $isDisabled
     */
    public function __construct($isDisabled = false)
    {
        $this->isDisabled = $isDisabled;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return $this->isDisabled ? '1' : '0';
    }
}
