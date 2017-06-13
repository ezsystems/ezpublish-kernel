<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\ContentType\ContentType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use eZ\Publish\Core\Repository\Values\MultiLanguageDescriptionTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageNameTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageTrait;

/**
 * this class represents a content type value.
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
 * @property-read bool $isContainer This flag hints to UIs if type may have children or not.
 * @property-read string $mainLanguageCode the main language of the content type names and description used for fallback.
 * @property-read bool $defaultAlwaysAvailable if an instance of a content type is created the always available flag is set by default this this value.
 *
 * @property-read int $defaultSortField Specifies which property the child locations should be sorted on by default when created. Valid values are found at {@link Location::SORT_FIELD_*}
 * @property-read int $defaultSortOrder Specifies whether the sort order should be ascending or descending by default when created. Valid values are {@link Location::SORT_ORDER_*}
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ContentType extends APIContentType
{
    use MultiLanguageTrait;
    use MultiLanguageNameTrait;
    use MultiLanguageDescriptionTrait;

    /**
     * Holds the collection of contenttypegroups the contenttype is assigned to.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    protected $contentTypeGroups = [];

    /**
     * Contains the content type field definitions from this type.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    protected $fieldDefinitions = [];

    /**
     * Field definitions indexed by identifier.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    protected $fieldDefinitionsByIdentifier = [];

    /**
     * Field definitions indexed by id.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    protected $fieldDefinitionsById = [];

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        // fieldDefinitions property comes from $data and is set in the ValueObject constructor
        foreach ($this->fieldDefinitions as $fieldDefinition) {
            $this->fieldDefinitionsByIdentifier[$fieldDefinition->identifier] = $fieldDefinition;
            $this->fieldDefinitionsById[$fieldDefinition->id] = $fieldDefinition;
        }
    }

    /**
     * This method returns the content type groups this content type is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        return $this->contentTypeGroups;
    }

    /**
     * This method returns the content type field definitions from this type.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function getFieldDefinitions()
    {
        return $this->fieldDefinitions;
    }

    /**
     * This method returns the field definition for the given identifier.
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getFieldDefinition($fieldDefinitionIdentifier)
    {
        if (isset($this->fieldDefinitionsByIdentifier[$fieldDefinitionIdentifier])) {
            return $this->fieldDefinitionsByIdentifier[$fieldDefinitionIdentifier];
        }

        return null;
    }

    /**
     * This method returns the field definition for the given id.
     *
     * @param mixed $fieldDefinitionId
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getFieldDefinitionById($fieldDefinitionId)
    {
        if (isset($this->fieldDefinitionsById[$fieldDefinitionId])) {
            return $this->fieldDefinitionsById[$fieldDefinitionId];
        }

        return null;
    }
}
