<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Mapper;

use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\Core\Search\Elasticsearch\Content\MapperInterface;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Elasticsearch\Content\Document;

/**
 * Standard Mapper implementation maps:
 *  - Content with its fields and corresponding Locations
 *  - Locations with Content data but without Content fields.
 */
class StandardMapper implements MapperInterface
{
    /**
     * Field name generator.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * Content handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Location handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Content type handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Object state handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * Section handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Field registry.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     */
    public function __construct(
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator,
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
    }

    /**
     * Maps given Content by given $contentId to a Document.
     *
     * @param int|string $contentId
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document
     */
    public function mapContentById($contentId)
    {
        $contentInfo = $this->contentHandler->loadContentInfo($contentId);

        return $this->mapContent(
            $this->contentHandler->load($contentId, $contentInfo->currentVersionNo)
        );
    }

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document
     */
    public function mapContent(Content $content)
    {
        $locations = $this->locationHandler->loadLocationsByContent($content->versionInfo->contentInfo->id);
        $section = $this->sectionHandler->load($content->versionInfo->contentInfo->sectionId);
        $mainLocation = null;
        $locationDocuments = [];
        foreach ($locations as $location) {
            $locationDocuments[] = $this->mapContentLocation($location, $content);
        }

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds(
            $content->versionInfo->contentInfo->ownerId
        );
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $content->versionInfo->contentInfo->ownerId;

        $fields = [
            new Field(
                'id',
                $content->versionInfo->contentInfo->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'type',
                $content->versionInfo->contentInfo->contentTypeId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'version',
                $content->versionInfo->versionNo,
                new FieldType\IdentifierField()
            ),
            new Field(
                'status',
                $content->versionInfo->status,
                new FieldType\IdentifierField()
            ),
            new Field(
                'name',
                $content->versionInfo->contentInfo->name,
                new FieldType\StringField()
            ),
            new Field(
                'creator',
                $content->versionInfo->creatorId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'owner',
                $content->versionInfo->contentInfo->ownerId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'owner_user_group',
                $ancestorLocationsContentIds,
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'section',
                $content->versionInfo->contentInfo->sectionId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'section_identifier',
                $section->identifier,
                new FieldType\IdentifierField()
            ),
            new Field(
                'section_name',
                $section->name,
                new FieldType\StringField()
            ),
            new Field(
                'remote_id',
                $content->versionInfo->contentInfo->remoteId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'modified',
                $content->versionInfo->contentInfo->modificationDate,
                new FieldType\DateField()
            ),
            new Field(
                'published',
                $content->versionInfo->contentInfo->publicationDate,
                new FieldType\DateField()
            ),
            new Field(
                'language_code',
                array_keys($content->versionInfo->names),
                new FieldType\MultipleStringField()
            ),
            new Field(
                'always_available',
                $content->versionInfo->contentInfo->alwaysAvailable,
                new FieldType\BooleanField()
            ),
            new Field(
                'locations',
                $locationDocuments,
                new FieldType\DocumentField()
            ),
        ];

        $contentType = $this->contentTypeHandler->load($content->versionInfo->contentInfo->contentTypeId);
        $fields[] = new Field(
            'group',
            $contentType->groupIds,
            new FieldType\MultipleIdentifierField()
        );
        $fields[] = new Field(
            'fields',
            $this->mapFields($content, $contentType),
            new FieldType\DocumentField()
        );

        $fields[] = new Field(
            'object_state',
            $this->getObjectStateIds($content->versionInfo->contentInfo->id),
            new FieldType\MultipleIdentifierField()
        );

        $document = new Document(
            [
                'id' => $content->versionInfo->contentInfo->id,
                'type' => 'content',
                'fields' => $fields,
            ]
        );

        return $document;
    }

    /**
     * Returns an array of documents containing fields for the given $content.
     * Each document contains fields for a single language.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document[]
     */
    protected function mapFields(Content $content, Type $contentType)
    {
        $fieldMap = [];

        foreach ($content->fields as $field) {
            $fieldMap[$field->languageCode][] = $field;
        }

        $fieldDocuments = [];

        foreach (array_keys($fieldMap) as $languageCode) {
            $fields = [];
            $fields[] = new Field(
                'meta_language_code',
                $languageCode,
                new FieldType\StringField()
            );
            $fields[] = new Field(
                'meta_is_main_translation',
                $content->versionInfo->contentInfo->mainLanguageCode === $languageCode,
                new FieldType\BooleanField()
            );
            $fields[] = new Field(
                'meta_is_always_available',
                (
                    $content->versionInfo->contentInfo->mainLanguageCode === $languageCode &&
                    $content->versionInfo->contentInfo->alwaysAvailable
                ),
                new FieldType\BooleanField()
            );

            foreach ($fieldMap[$languageCode] as $field) {
                foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                    if ($fieldDefinition->id !== $field->fieldDefinitionId) {
                        continue;
                    }

                    $fieldType = $this->fieldRegistry->getType($field->type);
                    $indexFields = $fieldType->getIndexData($field, $fieldDefinition);

                    foreach ($indexFields as $indexField) {
                        $fields[] = new Field(
                            $name = $this->fieldNameGenerator->getName(
                                $indexField->name,
                                $fieldDefinition->identifier,
                                $contentType->identifier
                            ),
                            $indexField->value,
                            $indexField->type
                        );

                        if ($indexField->type instanceof FieldType\FullTextField && $fieldDefinition->isSearchable) {
                            $fields[] = new Field(
                                $name . '_meta_all_' . str_replace('-', '_', $languageCode),
                                $indexField->value,
                                $indexField->type
                            );
                        }
                    }
                }
            }

            $fieldDocuments[] = new Document(
                [
                    'fields' => $fields,
                ]
            );
        }

        return $fieldDocuments;
    }

    /**
     * Maps given $location of a $content to a Document.
     *
     * Returned Location Document is to be used as nested Document of a Content, not searchable
     * directly but only through Content Search.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document
     */
    protected function mapContentLocation(Location $location, Content $content)
    {
        $fields = [
            new Field(
                'id',
                $location->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'priority',
                $location->priority,
                new FieldType\IntegerField()
            ),
            new Field(
                'hidden',
                $location->hidden,
                new FieldType\BooleanField()
            ),
            new Field(
                'invisible',
                $location->invisible,
                new FieldType\BooleanField()
            ),
            new Field(
                'remote_id',
                $location->remoteId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'parent_id',
                $location->parentId,
                new FieldType\IdentifierField()
            ),
            new Field(
                'path_string',
                $location->pathString,
                new FieldType\IdentifierField()
            ),
            new Field(
                'depth',
                $location->depth,
                new FieldType\IntegerField()
            ),
            new Field(
                'sort_field',
                $location->sortField,
                new FieldType\IdentifierField()
            ),
            new Field(
                'sort_order',
                $location->sortOrder,
                new FieldType\IdentifierField()
            ),
            new Field(
                'is_main_location',
                ($location->id == $content->versionInfo->contentInfo->mainLocationId),
                new FieldType\BooleanField()
            ),
        ];

        return new Document(
            [
                'fields' => $fields,
            ]
        );
    }

    /**
     * Maps given Location to a Document.
     *
     * Returned Document represents a "parent" Location document searchable with Location Search.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     *
     * @return \eZ\Publish\Core\Search\Elasticsearch\Content\Document
     */
    public function mapLocation(Location $location)
    {
        $contentInfo = $this->contentHandler->loadContentInfo($location->contentId);
        $content = $this->contentHandler->load($location->contentId, $contentInfo->currentVersionNo);
        $section = $this->sectionHandler->load($content->versionInfo->contentInfo->sectionId);

        $document = $this->mapContentLocation($location, $content);
        $document->id = $location->id;
        $document->type = 'location';

        $document->fields[] = new Field(
            'is_main_location',
            ($location->id == $content->versionInfo->contentInfo->mainLocationId),
            new FieldType\BooleanField()
        );

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds($contentInfo->ownerId);
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $contentInfo->ownerId;
        $document->fields[] = new Field(
            'content_owner_user_group',
            $ancestorLocationsContentIds,
            new FieldType\MultipleIdentifierField()
        );

        $document->fields[] = new Field(
            'content_id',
            $content->versionInfo->contentInfo->id,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_type',
            $content->versionInfo->contentInfo->contentTypeId,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_version',
            $content->versionInfo->versionNo,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_status',
            $content->versionInfo->status,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_name',
            $content->versionInfo->contentInfo->name,
            new FieldType\StringField()
        );
        $document->fields[] = new Field(
            'content_creator',
            $content->versionInfo->creatorId,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_owner',
            $content->versionInfo->contentInfo->ownerId,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_section',
            $content->versionInfo->contentInfo->sectionId,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_section_identifier',
            $section->identifier,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_section_name',
            $section->name,
            new FieldType\StringField()
        );
        $document->fields[] = new Field(
            'content_remote_id',
            $content->versionInfo->contentInfo->remoteId,
            new FieldType\IdentifierField()
        );
        $document->fields[] = new Field(
            'content_modified',
            $content->versionInfo->contentInfo->modificationDate,
            new FieldType\DateField()
        );
        $document->fields[] = new Field(
            'content_published',
            $content->versionInfo->contentInfo->publicationDate,
            new FieldType\DateField()
        );
        $document->fields[] = new Field(
            'content_language_code',
            array_keys($content->versionInfo->names),
            new FieldType\MultipleStringField()
        );
        $document->fields[] = new Field(
            'content_always_available',
            $content->versionInfo->contentInfo->alwaysAvailable,
            new FieldType\BooleanField()
        );

        $contentType = $this->contentTypeHandler->load($content->versionInfo->contentInfo->contentTypeId);
        $document->fields[] = new Field(
            'content_group',
            $contentType->groupIds,
            new FieldType\MultipleIdentifierField()
        );

        $document->fields[] = new Field(
            'content_object_state',
            $this->getObjectStateIds($content->versionInfo->contentInfo->id),
            new FieldType\MultipleIdentifierField()
        );

        return $document;
    }

    /**
     * Returns Content ids of all ancestor Locations of all Locations
     * of a Content with given $contentId.
     *
     * Used to determine user groups of a user with $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getAncestorLocationsContentIds($contentId)
    {
        $locations = $this->locationHandler->loadLocationsByContent($contentId);
        $ancestorLocationContentIds = [];
        $ancestorLocationIds = [];

        foreach ($locations as $location) {
            $locationIds = explode('/', trim($location->pathString, '/'));
            // Remove Location of Content with $contentId
            array_pop($locationIds);
            // Remove Root Location id (id==1 in legacy DB)
            array_shift($locationIds);

            $ancestorLocationIds = array_merge($ancestorLocationIds, $locationIds);
        }

        foreach (array_unique($ancestorLocationIds) as $locationId) {
            $location = $this->locationHandler->load($locationId);

            $ancestorLocationContentIds[$location->contentId] = true;
        }

        return array_keys($ancestorLocationContentIds);
    }

    /**
     * Returns an array of object state ids of a Content with given $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getObjectStateIds($contentId)
    {
        $objectStateIds = [];

        foreach ($this->objectStateHandler->loadAllGroups() as $objectStateGroup) {
            $objectStateIds[] = $this->objectStateHandler->getContentState(
                $contentId,
                $objectStateGroup->id
            )->id;
        }

        return $objectStateIds;
    }
}
