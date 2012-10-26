<?php
/**
 * File containing the Country Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Country;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Country field type
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
    public $countries = array();

    /**
     * Construct a new Value object and initialize it with given $data
     *
     * @param array[] $countries
     */
    public function __construct( array $countries = array() )
    {
        $this->countries = $countries;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return implode(
            ", ",
            array_map(
                function ( $country )
                {
                    return $country["Name"];
                },
                $this->countries
            )
        );
    }
}
