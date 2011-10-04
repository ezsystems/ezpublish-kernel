<?php
/**
 * File containing the CountryValueValidator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Country;
use ezp\Content\FieldType\Validator,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Configuration;

/**
 * Validate country value against configuration.
 */
class CountryValueValidator extends Validator
{
    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against aconstaint has failed, an entry will be added to the
     * $errors array.
     *
     * @param \ezp\Content\FieldType\Country\Value $value
     * @return bool
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        $countries = array_flip( Configuration::getInstance( "content" )->get( "CountrySettings", "Countries" ) );

        foreach ( $value->values as $country )
        {
            if ( !isset( $countries[$country] ) )
            {
                $this->errors[] = "\"$country\" is not a valid country name.";
                $isValid = false;
            }
        }

        return $isValid;
    }
}
