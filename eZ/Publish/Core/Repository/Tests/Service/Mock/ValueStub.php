<?php

/**
 * File containing the ValueStub class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for TextLine field type.
 */
class ValueStub extends BaseValue
{
    /** @var string */
    public $value;

    /**
     * Construct a new Value object and initialize it $value.
     *
     * @param string $value
     */
    public function __construct($value)
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
