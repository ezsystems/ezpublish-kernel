<?php

/**
 * File containing the Country InvalidValue Exception class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Country\Exception;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Exception thrown if an invalid identifier is used for a country.
 */
class InvalidValue extends InvalidArgumentException
{
    /**
     * Creates a new exception when $value is invalid.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        parent::__construct('$value', "'" . var_export($value, true) . "' is not a valid country identifier");
    }
}
