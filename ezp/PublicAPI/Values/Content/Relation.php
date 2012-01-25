<?php
namespace ezp\PublicAPI\Values\Content;
use ezp\PublicAPI\Values\ValueObject;
use ezp\PublicAPI\Values\Content\Content;

/**
 * Class representing a relation between content.
 */
class Relation extends ValueObject
{
    /**
     * The relation type COMMON is a general relation between object set by a user.
     *
     * @var int
     */
    const COMMON = 1;

    /**
     * the relation type EMBED is set for a relation which is anchored as embedded link in an attribute value
     *
     * @var int
     */
    const EMBED = 2;

    /**
     * the relation type LINK is set for a relation which is anchored as link in an attribute value
     *
     * @var int
     */
    const LINK = 4;

    /**
     * the relation type ATTRIBUTE is set for a relation which is part of an relation attribute value
     *
     * @var int
     */
    const ATTRIBUTE = 8;


    /**
     * Id of the relation
     *
     * @var mixed
     */
    public $id;


    /**
     * Source Content Type Field Definition Id.
     * For relation not of type RelationType::COMMON this field denotes the field definition id
     * of the attribute where the realtion is anchored.
     *
     * @var string
     */
    public $sourceFieldDefinitionIdentifier;

    /**
     * the content of the source of the relation
     * 
     * @var Content
     */
    public $sourceContent;

    /**
     * Destination Content
     *
     * @var Content
     */
    public $destinationContent;

    /**
     * The relation type bitmask
     *
     * @see Relation::COMMON, Relation::EMBED, Relation::LINK, Relation::ATTRIBUTE
     *
     * @var int
     */
    public $type;
}
