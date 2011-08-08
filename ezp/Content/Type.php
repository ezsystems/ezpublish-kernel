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
    ezp\Base\TypeCollection,
    ezp\Persistence\Content\Type as TypeValue;

/**
 * Type class ( Content Class )
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
 * @property-read string $remoteId
 * @property string $urlAliasSchema
 * @property string $nameSchema
 * @property bool $isContainer
 * @property bool $initialLanguageId
 * @property-read bool $contentTypeGroupIds
 * @property-read Type\Field[] $fields
 * @property-read Type\Group[] $groups
 */
class Type extends Model
{
    /**
     * @var array List of VO properties on this object
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
        'remoteId' => false,
        'urlAliasSchema' => true,
        'nameSchema' => true,
        'isContainer' => true,
        'initialLanguageId' => true,
        'contentTypeGroupIds' => false,
    );

    /**
     * @var array List of dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fields' => true,
        'groups' => true,
    );

    /**
     * @var Type\Field[]
     */
    protected $_fields;

    /**
     * @var Type\Group[]
     */
    protected $_groups;

    /**
     * Construct type and init all internal objects
     */
    public function __construct()
    {
        $this->properties = new TypeValue();
        $this->_fields = new TypeCollection( 'ezp\\Content\\Type\\Field' );
        $this->_groups = new TypeCollection( 'ezp\\Content\\Type\\Group' );
    }

    /**
     * @return Type\Field[]
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * @return Type\Group[]
     */
    public function getGroup()
    {
        return $this->_groups;
    }
}
?>
