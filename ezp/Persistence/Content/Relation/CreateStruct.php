<?php
/**
 * File containing the Relation CreateStruct class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Relation;
use ezp\Persistence\ValueObject;

/**
 * CreateStruct representing a relation between content.
 */
class CreateStruct extends ValueObject
{
    /**
     * Source Content ID
     *
     * @var mixed
     */
    public $sourceContentId;

    /**
     * Source Content Version number
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
