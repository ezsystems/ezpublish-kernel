<?php
/**
 * File containing Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Persistence\Content\Type as TypeValue;

/**
 * Type class ( Content Class )
 *
 *
 * @property-read mixed $id
 * @property-read int $status
 * @property string $name
 * @property string $description
 * @property string $identifier
 * @property mixed $created
 * @property int $creatorId
 * @property mixed $modified
 * @property int $modifierId
 * @property-read string $remoteId
 * @property string $urlAliasSchema
 * @property string $nameSchema
 * @property bool $isContainer
 * @property int $initialLanguageId
 * @property-read int[] $groupIds
 * @property-read Type\FieldDefinition[] $fields
 * @property-read Type\Group[] $groups
 */
class Type extends Model
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
    );

    /**
     * @var array List of dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fields' => true,
        'groups' => true,
    );

    /**
     * @var Type\FieldDefinition[]
     */
    protected $fields;

    /**
     * @var Type\Group[]
     */
    protected $groups;

    /**
     * Construct type and init all internal objects
     */
    public function __construct()
    {
        $this->properties = new TypeValue( array( 'status' => TypeValue::STATUS_DRAFT ) );
        $this->fields = new TypeCollection( 'ezp\\Content\\Type\\FieldDefinition' );
        $this->groups = new TypeCollection( 'ezp\\Content\\Type\\Group' );
    }

    /**
     * @return Type\FieldDefinition[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return Type\Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
?>
