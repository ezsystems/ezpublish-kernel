<?php

/**
 * File containing the ContentType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Values\ContentType;

use eZ\Publish\Core\Repository\Values\MultiLanguageDescriptionTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageNameTrait;
use eZ\Publish\Core\Repository\Values\MultiLanguageTrait;
use eZ\Publish\Core\REST\Client\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;

/**
 * this class represents a content type value.
 *
 * @property-read array $names calls getNames() or on access getName($language)
 * @property-read array $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read array $contentTypeGroups calls getContentTypeGroups
 * @property-read array $fieldDefinitions calls getFieldDefinitions() or on access getFieldDefinition($fieldDefIdentifier)
 * @property-read int $id the id of the content type
 * @property-read int $status the status of the content type. One of ContentType::STATUS_DEFINED|ContentType::STATUS_DRAFT|ContentType::STATUS_MODIFIED
 * @property-read string $identifier the identifier of the content type
 * @property-read \DateTime $creationDate the date of the creation of this content type
 * @property-read \DateTime $modificationDate the date of the last modification of this content type
 * @property-read int $creatorId the user id of the creator of this content type
 * @property-read int $modifierId the user id of the user which has last modified this content type
 * @property-read string $remoteId a global unique id of the content object
 * @property-read string $urlAliasSchema URL alias schema. If nothing is provided, $nameSchema will be used instead.
 * @property-read string $nameSchema  The name schema.
 * @property-read bool $isContainer Determines if the type is allowed to have children
 * @property-read string $mainLanguageCode the main language of the content type names and description used for fallback.
 * @property-read bool $defaultAlwaysAvailable if an instance of a content type is created the always available flag is set by default this this value.
 *
 * @property-read int $defaultSortField Specifies which property the child locations should be sorted on by default when created. Valid values are found at {@link Location::SORT_FIELD_*}
 * @property-read int $defaultSortOrder Specifies whether the sort order should be ascending or descending by default when created. Valid values are {@link Location::SORT_ORDER_*}
 *
 * @todo Implement access to field definitions (array and by identifier)
 * @todo Implement fetching of content type groups
 */
class ContentType extends APIContentType
{
    use MultiLanguageTrait;
    use MultiLanguageNameTrait;
    use MultiLanguageDescriptionTrait;

    /**
     * Content type service to fetch additional information from.
     *
     * @var \eZ\Publish\Core\REST\Client\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Carries the URL for the list of FieldDefinitions for the type.
     *
     * @var string
     */
    protected $fieldDefinitionListReference;

    /**
     * Contains the URL for the list of ContentTypeGroups for the ContentType.
     *
     * @var string
     */
    protected $contentTypeGroupListReference;

    /**
     * @param ContentTypeService $contentTypeService
     * @param array $data
     */
    public function __construct(ContentTypeService $contentTypeService, array $data = array())
    {
        $this->contentTypeService = $contentTypeService;
        parent::__construct($data);
    }

    /**
     * This method returns the content type groups this content type is assigned to.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public function getContentTypeGroups()
    {
        $contentTypeGroupList = $this->contentTypeService->loadContentTypeGroupList(
            $this->contentTypeGroupListReference
        );

        return $contentTypeGroupList->getContentTypeGroups();
    }

    /**
     * This method returns the content type field definitions from this type.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function getFieldDefinitions()
    {
        $fieldDefinitionList = $this->contentTypeService->loadFieldDefinitionList(
            $this->fieldDefinitionListReference
        );

        return $fieldDefinitionList->getFieldDefinitions();
    }

    /**
     * This method returns the field definition for the given identifier.
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return FieldDefinition
     */
    public function getFieldDefinition($fieldDefinitionIdentifier)
    {
        $fieldDefinitions = $this->getFieldDefinitions();
        foreach ($fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition->identifier === $fieldDefinitionIdentifier) {
                return $fieldDefinition;
            }
        }

        return null;
    }

    /**
     * Magic getter for retrieving convenience properties.
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get($property)
    {
        switch ($property) {
            case 'contentTypeGroups':
                return $this->getContentTypeGroups();
        }

        return parent::__get($property);
    }

    /**
     * Magic isset for signaling existence of convenience properties.
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        if ($property === 'contentTypeGroups') {
            return true;
        }

        return parent::__isset($property);
    }
}
