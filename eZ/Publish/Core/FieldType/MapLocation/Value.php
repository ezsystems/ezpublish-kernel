<?php

/**
 * File containing the MapLocation Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\MapLocation;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for MapLocation field type.
 */
class Value extends BaseValue
{
    /**
     * Latitude of the location.
     *
     * @var float
     */
    public $latitude;

    /**
     * Longitude of the location.
     *
     * @var float
     */
    public $longitude;

    /**
     * Display address for the location.
     *
     * @var string
     */
    public $address;

    /**
     * Construct a new Value object and initialize with $values.
     *
     * @param string[]|string $values
     */
    public function __construct(array $values = null)
    {
        foreach ((array)$values as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Returns a string representation of the keyword value.
     *
     * @return string A comma separated list of tags, eg: "php, eZ Publish, html5"
     */
    public function __toString()
    {
        if (is_array($this->address)) {
            return implode(', ', $this->address);
        }

        return (string)$this->address;
    }
}
