<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\ContentType\ContentType class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;

/**
 * this class represents a content type value
 *
 * @property-read \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups calls getContentTypeGroups
 * @property-read \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions calls getFieldDefinitions() or on access getFieldDefinition($fieldDefIdentifier)
 * @property-read mixed $id the id of the content type
 * @property-read int $status the status of the content type. One of ContentType::STATUS_DEFINED|ContentType::STATUS_DRAFT|ContentType::STATUS_MODIFIED
 * @property-read string $identifier the identifier of the content type
 * @property-read \DateTime $creationDate the date of the creation of this content type
 * @property-read \DateTime $modificationDate the date of the last modification of this content type
 * @property-read mixed $creatorId the user id of the creator of this content type
 * @property-read mixed $modifierId the user id of the user which has last modified this content type
 * @property-read string $remoteId a global unique id of the content object
 * @property-read string $urlAliasSchema URL alias schema. If nothing is provided, $nameSchema will be used instead.
 * @property-read string $nameSchema  The name schema.
 * @property-read boolean $isContainer Determines if the type is allowed to have children
 * @property-read string $mainLanguageCode the main language of the content type names and description used for fallback.
 * @property-read boolean $defaultAlwaysAvailable if an instance of a content type is created the always available flag is set by default this this value.
 * @property-read int $defaultSortField Specifies which property the child locations should be sorted on by default when created. Valid values are found at {@link Location::SORT_FIELD_*}
 * @property-read int $defaultSortOrder Specifies whether the sort order should be ascending or descending by default when created. Valid values are {@link Location::SORT_ORDER_*}
 */
class ContentType extends APIContentType
{
    /**
     * Holds the collection of names with languageCode keys
     *
     * @var string[]
     */
    protected $names;

    /**
     * Holds the collection of descriptions with languageCode keys
     *
     * @var string[]
     */
    protected $descriptions;

    /**
     * Holds the collection of contenttypegroups the contenttype is assigned to
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    protected $contentTypeGroups;

    /**
     * Contains the content type field definitions from this type
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    protected $fieldDefinitions;

    /**
     * Field definitions indexed by identifier
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    protected $fieldDefinitionsByIdentifier;

    /**
     * Field definitions indexed by id
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    protected $fieldDefinitionsById;

    function __construct( array $data = array() )
    {
        foreach ( $data as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
        foreach ( $this->fieldDefinitions as $fieldDefinition )
        {
            $this->fieldDefinitionsByIdentifier[$fieldDefinition->identifier] = $fieldDefinition;
            $this->fieldDefinitionsById[$fieldDefinition->id] = $fieldDefinition;
        }
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
        return $this->names;
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
        if ( isset( $this->names[$languageCode] ) )
        {
            return $this->names[$languageCode];
        }

        return null;
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
        return $this->descriptions;
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
        if ( isset( $languageCode, $this->descriptions ) )
        {
            return $this->descriptions[$languageCode];
        }

        return null;
    }

    /**
     * This method returns the content type groups this content type is assigned to
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        return $this->contentTypeGroups;
    }

    /**
     * This method returns the content type field definitions from this type
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function getFieldDefinitions()
    {
        return $this->fieldDefinitions;
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
        if ( isset( $this->fieldDefinitionsByIdentifier[$fieldDefinitionIdentifier] ) )
        {
            return $this->fieldDefinitionsByIdentifier[$fieldDefinitionIdentifier];
        }

        return null;
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
        if ( isset( $this->fieldDefinitionsById[$fieldDefinitionId] ) )
        {
            return $this->fieldDefinitionsById[$fieldDefinitionId];
        }

        return null;
    }
}
