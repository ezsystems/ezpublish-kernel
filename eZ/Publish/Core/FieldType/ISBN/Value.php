<?php

/**
 * File containing the ISBN Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version
 */
namespace eZ\Publish\Core\FieldType\ISBN;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for ISBN field type.
 */
class Value extends BaseValue
{
    /**
     * ISBN content.
     *
     * @var string
     */
    public $isbn;

    /**
     * Construct a new Value object and initialize it with its $isbn.
     *
     * @param string $isbn
     */
    public function __construct($isbn = '')
    {
        $this->isbn = $isbn;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->isbn;
    }
}
