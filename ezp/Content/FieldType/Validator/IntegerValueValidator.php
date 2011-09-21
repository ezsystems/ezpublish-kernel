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

        if ( $this->constraints['maxIntegerValue'] !== null && $value > $this->constraints['maxIntegerValue'] )
        {
            $this->errors[] = "The value can not be higher than {$this->constraints['maxIntegerValue']}.";
            $isValid = false;
        }

        if ( $this->constraints['minIntegerValue'] !== null && $value < $this->constraints['minIntegerValue'] )
        {
            $this->errors[] = "The value can not be lower than {$this->constraints['minIntegerValue']}.";
            $isValid = false;
        }

        return $isValid;
    }
}
