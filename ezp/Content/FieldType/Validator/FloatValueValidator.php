<?php
/**
 * File containing the FloatValueValidator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Validator;
use ezp\Content\FieldType\Validator;

/**
 * Validator to validate ranges in float values.
 *
 * Note that this validator can be limited by limitation on precision when
 * dealing with floating point numbers, and conversions.
 */
class FloatValueValidator extends Validator
{
    /**
     * Minimum value for float.
     *
     * @var float
     */
    public $minFloatValue;

    /**
     * Maximum value for float.
     *
     * @var float
     */
    public $maxFloatValue;

    /**
     * Returns the name of the validator.
     *
     * @return string
     */
    public function name()
    {
        return 'FloatValueValidator';
    }

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against aconstaint has failed, an entry will be added to the
     * $errors array.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate( $value )
    {
        $isValid = true;

        if ( $this->maxFloatValue !== null && $value > $this->maxFloatValue )
        {
            $this->errors[] = "The value can not be higher than {$this->maxFloatValue}.";
            $isValid = false;
        }

        if ( $this->minFloatValue !== null && $value < $this->minFloatValue )
        {
            $this->errors[] = "The value can not be lower than {$this->minFloatValue}.";
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
                'maxFloatValue' => $this->maxFloatValue,
                'minFloatValue' => $this->minFloatValue,
        ));
    }

}
