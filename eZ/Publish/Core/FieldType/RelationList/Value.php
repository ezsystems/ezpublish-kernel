<?php
/**
 * File containing the RelationList FieldType Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RelationList;

use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for RelationList field type
 */
class Value extends BaseValue
{
    /**
     * Related content id's
     *
     * @var mixed[]
     */
    public $destinationContentIds;

    /**
     * Construct a new Value object and initialize it $text
     *
     * @param mixed[] $destinationContentIds
     */
    public function __construct( array $destinationContentIds = array() )
    {
        $this->destinationContentIds = $destinationContentIds;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return implode( ',', $this->destinationContentIds );
    }
}
