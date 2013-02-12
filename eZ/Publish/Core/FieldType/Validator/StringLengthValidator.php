<?php
/**
 * File containing the StringLengthValidator class
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
 * Validator for checking min. and max. length of strings.
 *
 * @property int $maxStringLength The maximum allowed length of the string.
 * @property int $minStringLength The minimum allowed length of the string.
 */
class StringLengthValidator extends Validator
{
    protected $constraints = array(
        "maxStringLength" => false,
        "minStringLength" => false
    );

    protected $constraintsSchema = array(
        "minStringLength" => array(
            "type" => "int",
            "default" => 0
        ),
        "maxStringLength" => array(
            "type" => "int",
            "default" => null
        )
    );

    public function validateConstraints( $constraints )
    {
        $validationErrors = array();
        foreach ( $constraints as $name => $value )
        {
            switch ( $name )
            {
                case "minStringLength":
                case "maxStringLength":
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
     * Checks if the string $value is in desired range.
     *
     * The range is determined by $maxStringLength and $minStringLength.
     *
     * @param \eZ\Publish\Core\FieldType\TextLine\Value $value
     *
     * @return boolean
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxStringLength'] !== false &&
            $this->constraints['maxStringLength'] !== 0 &&
            strlen( $value->text ) > $this->constraints['maxStringLength'] )
        {
            $this->errors[] = new ValidationError(
                "The string can not exceed %size% character.",
                "The string can not exceed %size% characters.",
                array(
                    "size" => $this->constraints['maxStringLength']
                )
            );
            $isValid = false;
        }
        if ( $this->constraints['minStringLength'] !== false &&
            $this->constraints['minStringLength'] !== 0 &&
            strlen( $value->text ) < $this->constraints['minStringLength'] )
        {
            $this->errors[] = new ValidationError(
                "The string can not be shorter than %size% character.",
                "The string can not be shorter than %size% characters.",
                array(
                    "size" => $this->constraints['minStringLength']
                )
            );
            $isValid = false;
        }

        return $isValid;
    }
}
