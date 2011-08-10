<?php
/**
 * File contains Content Type Field (content class attribute) class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Model,
    ezp\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition as FieldDefinitionValue;

/**
 * Content Type Field (content class attribute) class
 *
 * @property-read mixed $id
 * @property string[] $name
 * @property string[] $description
 * @property string $identifier
 * @property string $fieldGroup
 * @property int $position
 * @property-read string $fieldType
 * @property bool $isTranslatable
 * @property bool $isRequired
 * @property bool $isInfoCollector
 * @property array $fieldTypeConstraints
 * @property mixed $defaultValue
 * @property-read \ezp\Content\Type $type
 */
class FieldDefinition extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'name' => true,
        'description' => true,
        'identifier' => true,
        'fieldGroup' => true,
        'position' => true,
        'fieldType' => false,
        'isTranslatable' => true,
        'isRequired' => true,
        'isInfoCollector' => true,
        'fieldTypeConstraints' => true,
        'defaultValue' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'type' => false,
    );

    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * Constructor, sets up value object, fieldType string and attach $contentType
     *
     * @param \ezp\Content\Type $contentType
     * @param string $fieldType
     */
    public function __construct( Type $contentType, $fieldType )
    {
        $this->contentType = $contentType;
        $this->properties = new FieldDefinitionValue( array( 'fieldType' => $fieldType ) );
    }

    /**
     * Return content type object
     *
     * @return \ezp\Content\Type
     */
    protected function getType()
    {
        return $this->contentType;
    }
}
