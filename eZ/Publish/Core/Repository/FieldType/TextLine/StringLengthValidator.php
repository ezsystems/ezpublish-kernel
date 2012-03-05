<?php
/**
 * File containing the StringLengthValidator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\TextLine;
use eZ\Publish\Core\Repository\FieldType\Validator,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue;

/**
 * Validator for checking min. and max. length of strings.
 *
 * @property int $maxStringLength The maximum allowed length of the string.
 * @property int $minStringLength The minimum required length of the string.
 */
class StringLengthValidator extends Validator
{
    protected $constraints = array(
        'maxStringLength' => false,
        'minStringLength' => false
    );

    /**
     * Checks if the string $value is in desired range.
     *
     * The range is determined by $maxStringLength and $minStringLength.
     *
     * @param \eZ\Publish\Core\Repository\FieldType\TextLine\Value $value
     * @return bool
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxStringLength'] !== false && strlen( $value->text ) > $this->constraints['maxStringLength'] )
        {
            $this->errors[] = "The string can not exceed {$this->constraints['maxStringLength']} characters.";
            $isValid = false;
        }

        if ( $this->constraints['minStringLength'] !== false && strlen( $value->text ) < $this->constraints['minStringLength'] )
        {
            $this->errors[] = "The string can not be shorter than {$this->constraints['minStringLength']} characters.";
            $isValid = false;
        }

        return $isValid;
    }
}
