<?php
/**
 * File containing the Selection Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Selection;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Selection field type
 */
class Value extends BaseValue
{
    /**
     * Selection content
     *
     * @var int[]
     */
    public $selection;

    /**
     * Construct a new Value object and initialize it $selection
     *
     * @param int[] $selection
     */
    public function __construct( array $selection = array() )
    {
        $this->selection = $selection;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return implode( ",", $this->selection );
    }
}
