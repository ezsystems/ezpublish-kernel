<?php
/**
 * File containing the ezp\Content\Relation class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Content,
    ezp\Base\Model,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Persistence\Content\Relation as RelationValue;

/**
 * This class represents a Content Relation
 *
 * @property-read mixed $id
 * @property-read mixed $sourceContentId
 * @property-read id $sourceContentVersion
 * @property-read mixed $sourceFieldDefinitionId
 * @property-read mixed $destinationContentId
 * @property-read int $type Bitmask
 * @property-read \ezp\Content $content Associated Content object
 */
class Relation extends Model
{
    // Those constants relates to eZContentObject::RELATION_*
    const COMMON = 1;
    const EMBED = 2;
    const LINK = 4;
    const ATTRIBUTE = 8;

    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        "id" => false,
        "sourceContentId" => true,
        "sourceContentVersion" => true,
        "sourceFieldDefinitionId" => true,
        "destinationContentId" => false,
        "type" => false,
    );

    /**
     * @var array List of dynamic properties on this object
     */
    protected $dynamicProperties = array(
        "content" => false,
    );

    /**
     * Associated Content object
     *
     * @var \ezp\Content
     */
    protected $content;

    /**
     * Setups a Relation object
     *
     * @param int $type
     * @param \ezp\Content $content
     */
    public function __construct( $type, Content $content )
    {
        if ( !is_int( $type ) || $type & ~( self::COMMON | self::EMBED | self::LINK | self::ATTRIBUTE ) )
            throw new InvalidArgumentValue( "type", $type );

        $this->properties = new RelationValue;
        $this->properties->destinationContentId = $content->id;
        $this->properties->type = $type;
        $this->content = $content;
    }

    /**
     * Returns the associated Content
     *
     * @return \ezp\Content
     */
    public function getContent()
    {
        return $this->content;
    }
}
