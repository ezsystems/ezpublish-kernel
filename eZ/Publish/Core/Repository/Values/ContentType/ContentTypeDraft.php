<?php
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission;

/**
 *
 * This class represents a draft of a content type
 *
 */
class ContentTypeDraft extends APIContentTypeDraft
{
    public function __get( $property )
    {
        if ( property_exists( $this->contentType, $property ) )
            return $this->contentType->$property;
        else if ( property_exists( $this, $property ) )
            return $this->$property;

        throw new PropertyNotFound( $property, get_class( $this ) );
    }

    public function __set( $property, $value )
    {
        if ( property_exists( $this->contentType, $property ) )
            $this->contentType->$property = $value;
        else if ( property_exists( $this, $property ) )
            throw new PropertyPermission( $property, PropertyPermission::READ, get_class( $this ) );

        throw new PropertyNotFound( $property, get_class( $this ) );
    }

    /**
     * Holds internal content type object
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     * @todo document
     */
    protected $contentType;

    /**
     * This method returns the human readable name in all provided languages
     * of the content type
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getNames()
    {
        return $this->contentType->getNames();
    }

    /**
     *
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none existis.
     */
    public function getName( $languageCode )
    {
        return $this->contentType->getName( $languageCode );
    }

    /**
     *  This method returns the human readable description of the content type
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->contentType->getDescriptions();
    }

    /**
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none existis.
     */
    public function getDescription( $languageCode )
    {
        return $this->contentType->getDescription( $languageCode );
    }

    /**
     * This method returns the content type groups this content type is assigned to
     * @return array an array of {@link ContentTypeGroup}
     */
    public function getContentTypeGroups()
    {
        return $this->contentType->contentTypeGroups;
    }

    /**
     * This method returns the content type field definitions from this type
     *
     * @return array an array of {@link FieldDefinition}
     */
    public function getFieldDefinitions()
    {
        return $this->contentType->getFieldDefinitions();
    }

    /**
     *
     * this method returns the field definition for the given identifier
     * @param $fieldDefinitionIdentifier
     * @return FieldDefinition
     */
    public function getFieldDefinition( $fieldDefinitionIdentifier )
    {
        return $this->contentType->getFieldDefinition( $fieldDefinitionIdentifier );
    }
}
