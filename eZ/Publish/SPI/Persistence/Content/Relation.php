<?php
/**
 * File containing the Relation class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

/**
 * Class representing a relation between content.
 */
class Relation extends ValueObject
{
    /**
     * Id of the relation
     *
     * @var mixed
     */
    public $id;

    /**
     * Source Content ID
     *
     * @var mixed
     */
    public $sourceContentId;

    /**
     * Source Content Version
     *
     * @var int
     */
    public $sourceContentVersionNo;

    /**
     * Source Content Type Field Definition Id
     *
     * @var mixed
     */
    public $sourceFieldDefinitionId;

    /**
     * Destination Content ID
     *
     * @var mixed
     */
    public $destinationContentId;

    /**
     * Type bitmask
     *
     * @see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE
     * @var int
     */
    public $type;
}
