<?php
namespace eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreate;

use eZ\Publish\API\Repository\Values\Content\Location;

use eZ\Publish\API\Repository\Tests\Stubs\Exceptions;

class ContentTypeCreateStructStub extends ContentTypeCreateStruct
{
    private $names;

    private $descriptions;

    private $fieldDefinitions;

    public function __construct( array $values = array() )
    {
        $this->names            = new \ArrayObject();
        $this->descriptions     = new \ArrayObject();
        $this->fieldDefinitions = new \ArrayObject();

        foreach ( $values as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
    }

    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case 'names':
            case 'descriptions':
            case 'fieldDefinitions':
                throw new Exceptions\PropertyReadOnlyExceptionStub( $propertyName );

            case 'name':
                $this->setName( $propertyValue );
                break;

            case 'description':
                $this->setDescription( $propertyValue );
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
            case 'fieldDefinitions':
                return $this->$propertyName;

            case 'name':
                return $this->names[$this->mainLanguageCode];

            case 'description':
                return $this->descriptions[$this->mainLanguageCode];
        }
        return parent::__get( $propertyName );
    }

    public function __isset( $propertyName )
    {
        switch( $propertyName )
        {
            case 'names':
            case 'name':
            case 'descriptions':
            case 'description':
            case 'fieldDefinitions':
                return true;
        }
        return parent::__isset( $propertyName );
    }

    /**
     * set a content type name for the given language
     *
     * @param string $name
     * @param string $language if not given the initialLanguage is used as default
     */
    public function setName( $name, $language = null )
    {
        if ( $language === null )
        {
            $language = $this->mainLanguageCode;
        }
        $this->names[$language] = $name;
    }

    /**
     * set a content type description for the given language
     *
     * @param string $description
     * @param string $language if not given the initialLanguage is used as default
     */
    public function setDescription( $description, $language = null )
    {
        if ( $language === null )
        {
            $language = $this->mainLanguageCode;
        }
        $this->descriptions[$language] = $description;
    }

    /**
     * adds a new field definition
     *
     * @param FieldDefinitionCreate $fieldDef
     */
    public function addFieldDefinition( /*FieldDefinitionCreate*/ $fieldDef )
    {
        $this->fieldDefinitions[] = $fieldDef;
    }
}
