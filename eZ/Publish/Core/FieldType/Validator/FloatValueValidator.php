<?php
/**
 * File containing the FloatValueValidator class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Validator;

use eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Validator to validate ranges in float values.
 *
 * Note that this validator can be limited by limitation on precision when
 * dealing with floating point numbers, and conversions.
 *
 * @property float $minFloatValue Minimum value for float.
 * @property float $maxFloatValue Maximum value for float.
 */
class FloatValueValidator extends Validator
{
    protected $constraints = array(
        'minFloatValue' => false,
        'maxFloatValue' => false
    );

    protected $constraintsSchema = array(
        "minFloatValue" => array(
            "type" => "float",
            "default" => false
        ),
        "maxFloatValue" => array(
            "type" => "float",
            "default" => false
        )
    );

    public function validateConstraints( $constraints )
    {
        $validationErrors = array();

        foreach ( $constraints as $name => $value )
        {
            switch ( $name )
            {
                case "minFloatValue":
                case "maxFloatValue":
                    if ( $value !== false && !is_numeric( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be of numeric type",
                            null,
                            array(
                                "parameter" => $name
                            )
                        );
                    }
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Validator parameter '%parameter%' is unknown",
                        null,
                        array(
                            "parameter" => $name
                        )
                    );
            }
        }

        return $validationErrors;
    }

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against a constant has failed, an entry will be added to the
     * $errors array.
     *
     * @param \eZ\Publish\Core\FieldType\Float\Value $value
     *
     * @return boolean
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxFloatValue'] !== false && $value->value > $this->constraints['maxFloatValue'] )
        {
            $this->errors[] = new ValidationError(
                "The value can not be higher than %size%.",
                null,
                array(
                    "size" => $this->constraints['maxFloatValue']
                )
            );
            $isValid = false;
        }

        if ( $this->constraints['minFloatValue'] !== false && $value->value < $this->constraints['minFloatValue'] )
        {
            $this->errors[] = new ValidationError(
                "The value can not be lower than %size%.",
                null,
                array(
                    "size" => $this->constraints['minFloatValue']
                )
            );
            $isValid = false;
        }

        return $isValid;
    }
}
