<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\Relation class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;

/**
 * Class representing a relation between content.
 *
 * @property-read mixed $id the internal id of the relation
 * @property-read string $sourceFieldDefinitionIdentifier the field definition identifier of the field where this relation is anchored if the relation is of type EMBED, LINK, or ATTRIBUTE
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $sourceContentInfo - calls {@link getSourceContentInfo()}
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContentInfo - calls {@link getDestinationContentInfo()}
 * @property-read int $type The relation type bitmask containing one or more of Relation::COMMON, Relation::EMBED, Relation::LINK, Relation::FIELD
 */
class Relation extends APIRelation
{
    /**
     * the content of the source content of the relation
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $sourceContentInfo;

    /**
     * the content of the destination content of the relation
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $destinationContentInfo;

    /**
     * the content of the source content of the relation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getSourceContentInfo()
    {
        return $this->sourceContentInfo;
    }

    /**
     * the content of the destination content of the relation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getDestinationContentInfo()
    {
        return $this->destinationContentInfo;
    }
}
