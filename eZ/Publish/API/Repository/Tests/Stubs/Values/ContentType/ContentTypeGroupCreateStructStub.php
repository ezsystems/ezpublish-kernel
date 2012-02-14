<?php
namespace eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;

class ContentTypeGroupCreateStructStub extends ContentTypeGroupCreateStruct
{
    private $names;

    private $descriptions;

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
                return $this->$propertyName;

            case 'name':
                return $this->names[$this>mainLanguageCode];

            case 'description':
                return $this->descriptions[$this>mainLanguageCode];
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
                return true;
        }
        return parent::__isset( $propertyName );
    }

    /**
     * 5.x only
     * set a content type group name for the given language
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
     * 5.x only
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
}
