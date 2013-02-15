<?php
/**
 * File containing the IntegerValueValidator class
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
 * Validate ranges of integer value.
 *
 * @property int $minIntegerValue The minimum allowed integer value.
 * @property int $maxIntegerValue The maximum allowed integer value.
 */
class IntegerValueValidator extends Validator
{
    protected $constraints = array(
        'minIntegerValue' => false,
        'maxIntegerValue' => false
    );

    protected $constraintsSchema = array(
        "minIntegerValue" => array(
            "type" => "int",
            "default" => 0
        ),
        "maxIntegerValue" => array(
            "type" => "int",
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
                case "minIntegerValue":
                case "maxIntegerValue":
                    if ( $value !== false && !is_integer( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be of integer type",
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
     * When a check against a constraint has failed, an entry will be added to the
     * $errors array.
     *
     * @param \eZ\Publish\Core\FieldType\Integer\Value $value
     *
     * @return boolean
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxIntegerValue'] !== false && $value->value > $this->constraints['maxIntegerValue'] )
        {
            $this->errors[] = new ValidationError(
                "The value can not be higher than %size%.",
                null,
                array(
                    "size" => $this->constraints['maxIntegerValue']
                )
            );
            $isValid = false;
        }

        if ( $this->constraints['minIntegerValue'] !== false && $value->value < $this->constraints['minIntegerValue'] )
        {
            $this->errors[] = new ValidationError(
                "The value can not be lower than %size%.",
                null,
                array(
                    "size" => $this->constraints['minIntegerValue']
                )
            );
            $isValid = false;
        }

        return $isValid;
    }
}
