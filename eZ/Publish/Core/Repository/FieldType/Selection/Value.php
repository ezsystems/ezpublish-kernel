<?php
/**
 * File containing the Selection Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Selection;
use eZ\Publish\Core\Repository\FieldType\ValueInterface,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue;

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
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( $stringValue );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        if ( !is_array( $this->selection ) )
            return "";

        return implode( ",", $this->selection );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        throw new \RuntimeException( 'Implement this method' );
    }
}
