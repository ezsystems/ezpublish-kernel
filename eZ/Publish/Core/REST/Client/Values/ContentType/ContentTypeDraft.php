<?php

namespace eZ\Publish\Core\REST\Client\Values\ContentType;

use eZ\Publish\API\Repository\Values;

/**
 *
 * This class represents a draft of a content type
 *
 */
class ContentTypeDraft extends \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
{
    /**
     * ContentType encapsulated in the draft
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected $innerContentType;

    /**
     * Creates a new draft with $innerContentType
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $innerContentType
     */
    public function __construct( Values\ContentType\ContentType $innerContentType )
    {
        $this->innerContentType = $innerContentType;
    }

    /**
     * Returns the inner content type.
     *
     * ONLY FOR INTERNAL USE IN THE INTEGRATION TEST SUITE.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function getInnerContentType()
    {
        return $this->innerContentType;
    }

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
     * This method returns the name of the content type in the given language
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none existis.
     */
    public function getName( $languageCode )
    {
        return $this->innerContentType->getName( $languageCode );
    }

    /**
     * This method returns the human readable description of the content type
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
     * This method returns the name of the content type in the given language
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none existis.
     */
    public function getDescription( $languageCode )
    {
        return $this->innerContentType->getDescription( $languageCode );
    }

    /**
     * This method returns the content type groups this content type is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        return $this->innerContentType->getContentTypeGroups();
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
     * This method returns the field definition for the given identifier
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getFieldDefinition( $fieldDefinitionIdentifier )
    {
        return $this->innerContentType->getFieldDefinition( $fieldDefinitionIdentifier );
    }

    public function __get( $propertyName )
    {
        return $this->innerContentType->$propertyName;
    }

    public function __set( $propertyName, $propertyValue )
    {
        $this->innerContentType->$propertyName = $propertyValue;
    }

    public function __isset( $propertyName )
    {
        return isset( $this->innerContentType->$propertyName );
    }
}
