<?php
/**
 * File containing the RelationList FieldType Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\RelationList;
use eZ\Publish\Core\Repository\FieldType\Value as BaseValue;

/**
 * Value for TextLine field type
 */
class Value extends BaseValue
{
    /**
     * Text content
     *
     * @var string
     */
    public $text;

    /**
     * Construct a new Value object and initialize it $text
     *
     * @param string $text
     */
    public function __construct( $text = '' )
    {
        $this->text = $text;
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->text;
    }
}
