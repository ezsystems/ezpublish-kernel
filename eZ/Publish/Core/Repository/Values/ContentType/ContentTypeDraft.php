<?php
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft;

/**
 *
 * This class represents a draft of a content type
 *
 */
class ContentTypeDraft extends APIContentTypeDraft
{
    /**
     * Magic getter for ruting get's to innerContentType
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get( $property )
    {
        return $this->innerContentType->$property;
    }

    /**
     * Magic set for ruting set calls to innerContentType
     *
     * @param string $property
     * @param mixed $propertyValue
     */
    public function __set( $property, $propertyValue )
    {
        $this->innerContentType->$property = $propertyValue;
    }

    /**
     * Magic isset for ruting isset calls to innerContentType
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset( $property )
    {
        return isset( $this->innerContentType->$property );
    }

    /**
     * Holds internal content type object
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     * @todo document
     */
    protected $innerContentType;

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
        return $this->innerContentType->getNames();
    }

    /**
     *
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none exists.
     */
    public function getName( $languageCode )
    {
        return $this->innerContentType->getName( $languageCode );
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
        return $this->innerContentType->getDescriptions();
    }

    /**
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none exists.
     */
    public function getDescription( $languageCode )
    {
        return $this->innerContentType->getDescription( $languageCode );
    }

    /**
     * This method returns the content type groups this content type is assigned to
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        return $this->innerContentType->contentTypeGroups;
    }

    /**
     * This method returns the content type field definitions from this type
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function getFieldDefinitions()
    {
        return $this->innerContentType->getFieldDefinitions();
    }

    /**
     *
     * this method returns the field definition for the given identifier
     * @param string $fieldDefinitionIdentifier
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getFieldDefinition( $fieldDefinitionIdentifier )
    {
        return $this->innerContentType->getFieldDefinition( $fieldDefinitionIdentifier );
    }
}
