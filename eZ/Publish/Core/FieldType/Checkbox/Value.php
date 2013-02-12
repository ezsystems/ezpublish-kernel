<?php
/**
 * File containing the Checkbox Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Checkbox;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Checkbox field type
 */
class Value extends BaseValue
{
    /**
     * Boolean value
     *
     * @var boolean
     */
    public $bool;

    /**
     * Construct a new Value object and initialize it $boolValue
     *
     * @param boolean $boolValue
     */
    public function __construct( $boolValue = false )
    {
        $this->bool = $boolValue;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     *
     * @return string
     */
    public function __toString()
    {
        return $this->bool ? '1' : '0';
    }
}
