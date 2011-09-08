<?php
/**
 * File containing the StringLengthValidator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Validator;
use ezp\Content\FieldType\Validator;

/**
 * Validator for checking min. and max. length of strings.
 */
class StringLengthValidator extends Validator
{
    /**
     * The maximum allowed length of the string.
     *
     * @var int
     */
    public $maxStringLength;

    /**
     * The minimum required length of the string.
     *
     * @var int
     */
    public $minStringLength;

    /**
     * Returns the name of the validator.
     *
     * @return string
     */
    public function name()
    {
        return 'StringLengthValidator';
    }

    /**
     * Checks if the string $value is in desired range.
     *
     * The range is determined by $maxStringLength and $minStringLength.
     *
     * @param string $value
     * @return bool
     */
    public function validate( $value )
    {
        $isValid = true;

        if ( $this->maxStringLength !== null && strlen( $value ) > $this->maxStringLength )
        {
            $this->errors[] = "The string can not exceed {$this->maxStringLength} characters.";
            $isValid = false;
        }

        if ( $this->minStringLength !== null && strlen( $value ) < $this->minStringLength )
        {
            $this->errors[] = "The string can not be shorter than {$this->minStringLength} characters.";
            $isValid = false;
        }
        
        return $isValid;
    }

    /**
     * Combines configurable constraints in the validator and creates a map.
     *
     * This map is then supposed to be used inside a FieldDefinition.
     *
     * @internal
     * @return array
     */
    public function getValidatorConstraints()
    {
        return array(
            $this->name() => array(
                'maxStringLength' => $this->maxStringLength,
                'minStringLength' => $this->minStringLength
            ));
    }

}
