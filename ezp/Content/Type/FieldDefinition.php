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
    ezp\Persistence\Content\Type\FieldDefinition as FieldDefinitionValue,
    ezp\Content\FieldType\Factory as FieldTypeFactory,
    ezp\Content\FieldType\Validator,
    ezp\Content\FieldType\Value as FieldValue,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue;

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
 * @property \ezp\Content\FieldType\Value $defaultValue
 * @property-read \ezp\Content\Type $contentType ContentType object
 * @property-read \ezp\Content\FieldType $type FieldType object
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
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'contentType' => false,
        'type' => false,
        'defaultValue' => true
    );

    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * @var \ezp\Content\FieldType
     */
    protected $type;

    /**
     * @var \ezp\Content\FieldType\Value
     */
    protected $defaultValue;

    /**
     * Constructor, sets up value object, fieldType string and attach $contentType
     *
     * @param \ezp\Content\Type $contentType
     * @param string $fieldType
     */
    public function __construct( Type $contentType, $fieldType )
    {
        $this->type = $contentType;
        $this->properties = new FieldDefinitionValue( array( 'fieldType' => $fieldType ) );
        $this->type = FieldTypeFactory::build( $fieldType );
        $this->defaultValue = $this->type->getValue();
        $this->attach( $this->type, 'field/setValue' );
    }

    /**
     * Return content type object
     *
     * @return \ezp\Content\Type
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Return field type object
     *
     * @return \ezp\Content\FieldType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Adds a validator to for this field.
     *
     * @param \ezp\Content\FieldType\Validator $validator
     * @return void
     */
    public function addValidator( Validator $validator )
    {
        // We'll initialize the map with constraints if it does not already exist.
        if ( empty( $this->fieldTypeConstraints ) )
        {
            $this->fieldTypeConstraints = array();
        }
        $this->type->fillConstraintsFromValidator( $this, $validator );
    }

    /**
     * Returns default value for current field definition
     *
     * @return \ezp\Content\FieldType\Value
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Sets a new default value for current field definition
     *
     * @param \ezp\Content\FieldType\Value $value
     */
    public function setDefaultValue( FieldValue $value )
    {
        $this->defaultValue = $value;
        $this->notify( 'field/setValue', array( 'value' => $value ) );
        $this->properties->defaultValue = new PersistenceFieldValue;
        $this->type->setFieldValue( $this->properties->defaultValue );
    }
}
