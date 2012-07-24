<?php
/**
 * File containing the Relation FieldType Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Relation;
use eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    eZ\Publish\Core\Repository\Values\Content\Relation;

/**
 * Value for TextLine field type
 */
class Value extends BaseValue
{
    /**
     * Text content
     *
     * @var \eZ\Publish\Core\Repository\Values\Content\Relation
     */
    public $relation;

    /**
     * Construct a new Value object and initialize it $text
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\ContentInfo $destinationContent
     */
    public function __construct( ContentInfo $destinationContent )
    {
        $this->text = $text;
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->relation->;
    }
}
