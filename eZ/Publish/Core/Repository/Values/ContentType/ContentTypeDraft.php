<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\ContentType\ContentTypeDraft class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft;

/**
 *
 * This class represents a draft of a content type
 */
class ContentTypeDraft extends APIContentTypeDraft
{
    /**
     * Function where list of properties are returned
     *
     * Override to add dynamic properties
     * @uses parent::getProperties()
     *
     * @param array $dynamicProperties
     *
     * @return array
     */
    protected function getProperties( $dynamicProperties = array( 'contentTypeGroups', 'fieldDefinitions' ) )
    {
        return parent::getProperties( $dynamicProperties );
    }

    /**
     * Magic getter for routing get calls to innerContentType
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
     * Magic set for routing set calls to innerContentType
     *
     * @param string $property
     * @param mixed $propertyValue
     */
    public function __set( $property, $propertyValue )
    {
        $this->innerContentType->$property = $propertyValue;
    }

    /**
     * Magic isset for routing isset calls to innerContentType
     *
     * @param string $property
     *
     * @return boolean
     */
    public function __isset( $property )
    {
        return $this->innerContentType->__isset( $property );
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
     * This method returns the name of the content type in the given language
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
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
     * @return string the description for the given language or null if none exists.
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

    /**
     * This method returns the field definition for the given id
     *
     * @param mixed $fieldDefinitionId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getFieldDefinitionById( $fieldDefinitionId )
    {
        return $this->innerContentType->getFieldDefinition( $fieldDefinitionId );
    }
}
