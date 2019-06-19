<?php

/**
 * File containing the Country Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Country;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Country field type.
 */
class Value extends BaseValue
{
    /**
     * Associative array with Alpha2 codes as keys and countries data as values.
     *
     * Example:
     * <code>
     *  array(
     *      "JP" => array(
     *          "Name" => "Japan",
     *          "Alpha2" => "JP",
     *          "Alpha3" => "JPN",
     *          "IDC" => 81
     *      )
     *  )
     * </code>
     *
     * @var array[]
     */
    public $countries = [];

    /**
     * Construct a new Value object and initialize it with given $data.
     *
     * @param array[] $countries
     */
    public function __construct(array $countries = [])
    {
        $this->countries = $countries;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return implode(', ', array_column($this->countries, 'Name'));
    }
}
