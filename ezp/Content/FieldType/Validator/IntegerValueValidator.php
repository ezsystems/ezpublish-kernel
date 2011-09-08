<?php
/**
 * File containing the IntegerValueValidator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Validator;
use ezp\Content\FieldType\Validator;

/**
 * Validate ranges of integer value.
 */
class IntegerValueValidator extends Validator
{
    /**
     * The minimum allowed integer value.
     * @var int
     */
    public $minIntegerValue;

    /**
     * The maximum allowed integer value.
     * @var int
     */
    public $maxIntegerValue;

    /**
     * Returns the name of the validator.
     *
     * @return string
     */
    public function name()
    {
        return 'IntegerValueValidator';
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

        if ( $this->maxIntegerValue !== null && $value > $this->maxIntegerValue )
        {
            $this->errors[] = "The value can not be higher than {$this->maxIntegerValue}.";
            $isValid = false;
        }

        if ( $this->minIntegerValue !== null && $value < $this->minIntegerValue )
        {
            $this->errors[] = "The value can not be lower than {$this->minIntegerValue}.";
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
                'maxIntegerValue' => $this->maxIntegerValue,
                'minIntegerValue' => $this->minIntegerValue,
        ));
    }

}
