<?php
/**
 * File containing Concrete Content Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Model,
    ezp\Base\Collection\ReadOnly as ReadOnlyCollection,
    ezp\Content\Type,
    ezp\Persistence\Content\Type as TypeValue;

/**
 * Concrete Content Type class
 *
 * @property-read mixed $id
 * @property-read int $status
 * @property string[] $name
 * @property string[] $description
 * @property string $identifier
 * @property mixed $created
 * @property mixed $creatorId
 * @property mixed $modified
 * @property mixed $modifierId
 * @property-read string $remoteId
 * @property string $urlAliasSchema
 * @property string $nameSchema
 * @property bool $isContainer
 * @property int $initialLanguageId
 * @property bool $defaultAlwaysAvailable
 * @property-read int[] $groupIds
 * @property-read Type\FieldDefinition[] $fields Appending items after it has been created has no effect, use TypeService->addFieldDefinition()
 * @property-read Type\Group[] $groups Appended items after it has been created has no effect, use TypeService->link()
 */
class Concrete extends Model implements Type
{
    /**
     * @var array List of VO properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'status' => false,
        'name' => true,
        'description' => true,
        'identifier' => true,
        'created' => true,
        'creatorId' => true,
        'modified' => true,
        'modifierId' => true,
        'remoteId' => false,
        'urlAliasSchema' => true,
        'nameSchema' => true,
        'isContainer' => true,
        'initialLanguageId' => true,
        'groupIds' => false,
        'defaultAlwaysAvailable' => true,
    );

    /**
     * @var array List of dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fields' => true,
        'groups' => true,
    );

    /**
     * @var ezp\Content\Type\FieldDefinition[]
     */
    protected $fields;

    /**
     * @var ezp\Content\Type\Group[]
     */
    protected $groups;

    /**
     * Construct type and init all internal objects
     */
    public function __construct()
    {
        $this->properties = new TypeValue();
        $this->fields = new ReadOnlyCollection();
        $this->groups = new ReadOnlyCollection();
    }

    /**
     * Returns definition of the content type object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return array(
            'module' => 'class',
            // @todo Add functions with group limitations
        );
    }

    /**
     * @return ezp\Content\Type\FieldDefinition[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return ezp\Content\Type\Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
