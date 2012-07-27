<?php
/**
 * File containing the Relation FieldType Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Relation;
use eZ\Publish\Core\FieldType\Value as BaseValue,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo;

/**
 * Value for Relation field type
 */
class Value extends BaseValue
{
    /**
     * Related content
     *
     * @var \eZ\Publish\Core\Repository\Values\Content\ContentInfo
     */
    public $destinationContentId;

    /**
     * Construct a new Value object and initialize it $destinationContent
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\ContentInfo $destinationContent Content the relation is to
     */
    public function __construct( $destinationContentId = null )
    {
        $this->destinationContentId = $destinationContentId;
    }

    /**
     * Returns the related content's name
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->destinationContentId;
    }
}
