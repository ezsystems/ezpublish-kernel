<?php
namespace eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

/**
 * this class is used to create a field definition
 * @property-write $names $names[$language] calls setName($language)
 * @property-write $name calls setName() for setting a namein the initial language
 * @property-write $descriptions $descriptions[$language] calls setDescription($language)
 * @property-write $description calls setDescription() for setting a description in an initial language
 */
class FieldDefinitionCreateStructStub extends FieldDefinitionCreateStruct
{
    private $names;

    private $descriptions;

    private $validator;

    private $fieldSettings;

    public function __construct( array $values = array() )
    {
        $this->names        = new \ArrayObject();
        $this->descriptions = new \ArrayObject();

        foreach ( $values as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
    }

    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case 'name':
                $this->setName( $propertyValue );
                break;
            case 'description':
                $this->setDescription( $propertyValue );
                break;
            case 'validator':
                $this->setValidator( $propertyValue );
                break;
            case 'fieldSettings':
                $this->setFieldSettings( $propertyValue );
                break;

            default:
                parent::__set( $propertyName, $propertyValue );
        }
    }

    public function __get( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'names':
            case 'descriptions':
            case 'validator':
            case 'fieldSettings':
                return $this->$propertyName;
        }
        return parent::__get( $propertyName );
    }

    public function __isset( $propertyName )
    {
        switch( $propertyName )
        {
            case 'names':
            case 'descriptions':
            case 'validator':
            case 'fieldSettings':
                return true;
        }
        return parent::__isset( $propertyName );
    }

    /**
     * set a field definition name for the given language
     *
     * @param string $name
     * @param string $language
     */
    public function setName( $name, $language )
    {
        if ( $language === null )
        {
            $language = $this->mainLanguageCode;
        }
        $this->names[$language] = $name;
    }

    /**
     * set a  fie definition description for the given language
     *
     * @param string $description
     * @param string $language
     */
    public function setDescription( $description, $language )
    {
        if ( $language === null )
        {
            $language = $this->mainLanguageCode;
        }
        $this->descriptions[$language] = $description;
    }

    /**
     * sets a validator which has to be supported by the field type
     *
     * @param Validator $validator
     */
    public function setValidator( /*Validator*/ $validator )
    {
        $this->validator = $validator;
    }

    /**
     * sets a field settings map supported by the field type
     *
     * @param array $fieldSettings
     */
    public function setFieldSettings( array $fieldSettings )
    {
        $this->fieldSettings = $fieldSettings;
    }
}
