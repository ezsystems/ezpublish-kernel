<?php
/**
 * File containing Concrete Content Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Group;
use ezp\Base\Model,
    ezp\Base\Collection\ReadOnly as ReadOnlyCollection,
    ezp\Content\Type\Group,
    ezp\Persistence\Content\Type\Group as GroupValue;


/**
 * Concrete Group class ( Content Class Group )
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
class Concrete extends Model implements Group
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
        $this->types = new ReadOnlyCollection();
    }

    /**
     * @return \ezp\Content\Type[]
     */
    public function getTypes()
    {
        return $this->types;
    }
}
