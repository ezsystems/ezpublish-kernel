<?php
/**
 * File containing the Selection Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Selection;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue;

/**
 * Value for Selection field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Selection content
     *
     * @var string[]
     */
    public $selection;

    /**
     * Construct a new Value object and initialize it $text
     *
     * @param string $text
     */
    public function __construct( $selection = array() )
    {
        $this->selection = (array)$selection;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( $stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return implode( ",", $this->selection );
    }
}
