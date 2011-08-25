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
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Persistence\Content\Type\Group as GroupValue;

/**
 * Group class ( Content Class Group )
 *
 *
 * @property-read int $id
 * @property string[] $name
 * @property string[] $description
 * @property string $identifier
 * @property mixed $created
 * @property mixed $creatorId
 * @property mixed $modified
 * @property mixed $modifierId
 * @property-read \ezp\Content\Type[] $types Appended items will not be stored, use TypeService->link()
 */
class Group extends Model
{
    /**
     * @var array List of read/Write VO properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
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
    protected $types;

    /**
     * Construct object with all internal objects
     */
    public function __construct()
    {
        $this->properties = new GroupValue();
        $this->types = new TypeCollection( 'ezp\\Content\\Type' );
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return $this->types;
    }
}
