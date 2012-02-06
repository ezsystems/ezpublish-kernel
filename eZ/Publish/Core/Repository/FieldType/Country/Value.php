<?php
/**
 * File containing the Country Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Country;
use eZ\Publish\Core\Repository\FieldType\ValueInterface,
    eZ\Publish\Core\Repository\FieldType\Country\Exception\InvalidValue,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    ezp\Base\Configuration;

/**
 * Value for Country field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Countries values
     *
     * @var array
     */
    protected $values;

    /**
     * Countries data
     *
     * @var array
     */
    private $data = array();

    /**
     * Construct a new Value object and initialize it with its $values
     *
     * @param string[] $values
     */
    public function __construct( $values = array() )
    {
        $this->values = (array)$values;

        if ( empty( $this->values ) )
            return;

        $countriesInfo = Configuration::getInstance( "country" )->getAll();
        foreach ( $this->values as $value )
        {
            foreach ( $countriesInfo as $countryInfo ) {
                switch ( $value ) {
                    case $countryInfo["Name"]:
                    case $countryInfo["Alpha2"]:
                    case $countryInfo["Alpha3"]:
                        $this->data[$countryInfo["Alpha2"]] = $countryInfo;
                        continue 3;
                }
            }

            throw new InvalidValue( $value );
        }
    }

    public function getCountriesInfo()
    {
        return $this->data;
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( array( $stringValue ) );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        return implode( ",", $this->values );
    }
}
