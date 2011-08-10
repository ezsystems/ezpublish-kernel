<?php
/**
 * Content Type group (content class group) domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Model,
    ezp\Base\TypeCollection,
    ezp\Persistence\Content\Type\Group as GroupValue;

/**
 * Group class ( Content Class Group )
 *
 *
 * @property-read int $id
 * @property-read int $version
 * @property string $name
 * @property string $description
 * @property string $identifier
 * @property mixed $created
 * @property string $creatorId
 * @property mixed $modified
 * @property string $modifierId
 * @property-read \ezp\Content\Type[] $types
 */
class Group extends Model
{
    /**
     * @var array List of read/Write VO properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'version' => false,
        'name' => true,
        'description' => true,
        'identifier' => true,
        'created' => true,
        'creatorId' => true,
        'modified' => true,
        'modifierId' => true,
    );

    /**
     * @var array List of dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'types' => true,
    );

    /**
     * @var \ezp\Content\Type[]
     */
    protected $contentTypes;

    /**
     * Construct object with all internal objects
     */
    public function __construct()
    {
        $this->properties = new GroupValue();
        $this->contentTypes = new TypeCollection( 'ezp\\Content\\Type' );
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->contentTypes;
    }
}
