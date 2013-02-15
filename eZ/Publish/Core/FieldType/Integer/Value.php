<?php
/**
 * File containing the Integer Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Integer;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Integer field type
 */
class Value extends BaseValue
{
    /**
     * Content of the value
     *
     * @var int
     */
    public $value;

    /**
     * Construct a new Value object and initialize with $value
     *
     * @param int $value
     */
    public function __construct( $value = null )
    {
        $this->value = $value;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
