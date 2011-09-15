<?php
/**
 * File containing the ezp\Content\Field class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Content\Version,
    ezp\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Field as FieldVO,
    ezp\Content\FieldType\Value as FieldValue;

/**
 * This class represents a Content's field
 *
 * @property-read mixed $id
 * @property-ready string $type
 * @property \ezp\Content\FieldType\Value $value Value for current field
 * @property string $type
 * @property mixed $language
 * @property-read int $versionNo
 * @property-read \ezp\Content\Version $version
 * @property-read \ezp\Content\Type\FieldDefinition $fieldDefinition
 */
class Field extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'type' => false,
        'language' => true,
        'versionNo' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'version' => false,
        'fieldDefinition' => false,
        'value' => true
    );

    /**
     * @var \ezp\Content\Version
     */
    protected $version;

    /**
     * @var \ezp\Content\Type\FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * @var \ezp\Content\FieldType\Value
     */
    protected $value;

    /**
     * Constructor, sets up properties
     *
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\FieldDefinition $fieldDefinition
     */
    public function __construct( Version $contentVersion, FieldDefinition $fieldDefinition )
    {
        $this->version = $contentVersion;
        $this->fieldDefinition = $fieldDefinition;
        $this->properties = new FieldVO(
            array(
                "type" => $fieldDefinition->fieldType,
                "fieldDefinitionId" => $fieldDefinition->id,
                //"value" => $fieldDefinition->defaultValue,
            )
        );
        $this->value = $fieldDefinition->defaultValue;
    }

    /**
     * Return content version object
     *
     * @return \ezp\Content\Version
     */
    protected function getVersion()
    {
        return $this->version;
    }

    /**
     * Return content type object
     *
     * @return \ezp\Content\Type\FieldDefinition
     */
    protected function getFieldDefinition()
    {
        return $this->fieldDefinition;
    }

    /**
     * Returns current field value as FieldValue object
     *
     * @return \ezp\Content\FieldType\Value
     */
    protected function getValue()
    {
        return $this->value;
    }

    /**
     * Assigns FieldValue object $inputValue to current field
     *
     * @param \ezp\Content\FieldType\Value $inputValue
     */
    protected function setValue( FieldValue $inputValue )
    {
        $this->value = $inputValue;
        $this->fieldDefinition->type->setValue( $inputValue );
    }
}
