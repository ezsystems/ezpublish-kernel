<?php
/**
 * File containing the StringLengthValidator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\Validator,
    eZ\Publish\Core\FieldType\ValidationError,
    eZ\Publish\Core\FieldType\Value as BaseValue;

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

    /**
     * Checks if the string $value is in desired range.
     *
     * The range is determined by $maxStringLength and $minStringLength.
     *
     * @param \eZ\Publish\Core\FieldType\TextLine\Value $value
     *
     * @return bool
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxStringLength'] !== false && strlen( $value->text ) > $this->constraints['maxStringLength'] )
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
        if ( $this->constraints['minStringLength'] !== false && strlen( $value->text ) < $this->constraints['minStringLength'] )
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
