<?php
/**
 * File containing the IntegerValueValidator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Integer;
use eZ\Publish\Core\Repository\FieldType\Validator,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue;

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
     * @param \eZ\Publish\Core\Repository\FieldType\Integer\Value $value
     * @return bool
     */
    public function validate( BaseValue $value )
    {
        $isValid = true;

        if ( $this->constraints['maxIntegerValue'] !== false && $value->value > $this->constraints['maxIntegerValue'] )
        {
            $this->errors[] = "The value can not be higher than {$this->constraints['maxIntegerValue']}.";
            $isValid = false;
        }

        if ( $this->constraints['minIntegerValue'] !== false && $value->value < $this->constraints['minIntegerValue'] )
        {
            $this->errors[] = "The value can not be lower than {$this->constraints['minIntegerValue']}.";
            $isValid = false;
        }

        return $isValid;
    }
}
