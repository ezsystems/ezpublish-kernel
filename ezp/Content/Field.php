<?php
/**
 * File containing the ezp\Content\Field class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\Exception\FieldValidation as FieldValidationException,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Observer,
    ezp\Base\Observable,
    ezp\Base\Repository,
    ezp\Content\Version,
    ezp\Content\Type\FieldDefinition,
    eZ\Publish\SPI\Persistence\Content\Field as FieldVO,
    ezp\Content\FieldType\Value as FieldValue,
    ezp\Content\FieldType\OnPublish as OnPublishFieldType,
    ezp\Content\FieldType\OnCreate as OnCreateFieldType;

/**
 * This class represents a Content's field
 *
 * @property-read mixed $id
 * @property-ready string $type
 * @property \ezp\Content\FieldType\Value $value Value for current field
 * @property string $type
 * @property mixed $language
 * @property-read int $versionNo
 * @property-read mixed $fieldDefinitionId
 * @property-read \ezp\Content\Version $version
 * @property-read \ezp\Content\Type\FieldDefinition $fieldDefinition
 */
class Field extends Model implements Observer
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'type' => false,
        'language' => true,
        'versionNo' => false,
        'fieldDefinitionId' => false,
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

        // Observer setup
        $fieldType = $this->fieldDefinition->getType();
        $this->attach( $fieldType, 'field/setValue' );

        if ( $fieldType instanceof OnPublishFieldType )
        {
            $this->attach( $fieldType, 'pre_publish' );
            $this->attach( $fieldType, 'post_publish' );
        }

        if ( $fieldType instanceof OnCreateFieldType )
        {
            $this->attach( $fieldType, 'pre_create' );
            $this->attach( $fieldType, 'post_create' );
        }

        $this->properties = new FieldVO(
            array(
                "type" => $fieldDefinition->fieldType,
                "fieldDefinitionId" => $fieldDefinition->id,
            )
        );
        $this->value = $fieldDefinition->defaultValue;
        $this->notify( 'field/setValue', array( 'value' => $this->value ) );
    }

    /**
     * Return content version object
     *
     * @return \ezp\Content\Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Return content type object
     *
     * @return \ezp\Content\Type\FieldDefinition
     */
    public function getFieldDefinition()
    {
        return $this->fieldDefinition;
    }

    /**
     * Returns current field value as FieldValue object
     *
     * @return \ezp\Content\FieldType\Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Assigns FieldValue object $inputValue to current field
     *
     * @param \ezp\Content\FieldType\Value $inputValue
     * @todo Make validate optional.
     */
    public function setValue( FieldValue $inputValue )
    {
        $this->value = $this->validateValue( $inputValue );
        $this->notify( 'field/setValue', array( 'value' => $inputValue ) );
    }

    /**
     * Validates $inputValue against validators registered in field definition.
     * If $inputValue is valid, it will be returned as is.
     * If not, a ValidationException will be thrown
     *
     * @todo Change so validate does not throw exceptions for logical validation errors.
     *
     * @param \ezp\Content\FieldType\FieldValue $inputValue
     * @return \ezp\Content\FieldType\FieldValue
     * @throws \ezp\Base\Exception\FieldValidation
     */
    protected function validateValue( FieldValue $inputValue )
    {
        $hasError = false;
        $errors = array();
        foreach ( $this->getFieldDefinition()->getValidators() as $validator )
        {
            if ( !$validator->validate( $inputValue ) )
            {
                $hasError = true;
                $errors = array_merge( $errors, $validator->getMessage() );
            }
        }

        if ( $hasError )
        {
            throw new FieldValidationException( $this->getFieldDefinition()->identifier, $errors );
        }

        return $inputValue;
    }

    /**
     * Called when subject has been updated
     * Supported events:
     *   - field/setValue Should be triggered when a field has been set a value. Will inject the value in the field type
     *
     * @param \ezp\Base\Observable $subject
     * @param string $event
     * @param array $arguments
     * @throws \ezp\Base\Exception\InvalidArgumentType If an expected observable argument isn't passed
     */
    public function update( Observable $subject, $event = 'update', array $arguments = null )
    {
        switch ( $event )
        {
            case 'pre_create':
            case 'post_create':
            case 'pre_publish':
            case 'post_publish':
                if ( !$subject instanceof Version )
                {
                    throw new InvalidArgumentType( 'version', 'ezp\\Content\\Version', null );
                }

                if ( !isset( $arguments['repository'] ) || !$arguments['repository'] instanceof Repository )
                {
                    throw new InvalidArgumentType( 'repository', 'ezp\\Base\\Repository', null );
                }

                // notify FieldTypes about the event
                $this->notify( $event, array( 'repository' => $arguments['repository'] ) );
                break;
        }
    }

    /**
     * Returns a string representation of the field value.
     * This string representation must be compatible with {@link self::fromString()} supported format
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value->__toString();
    }
}
