<?php
/**
 * File containing the Elasticsearch Mapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\SPI\Persistence\Content\Search\Field;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;

/**
 *
 */
class Mapper
{
    /**
     * Field name generator
     *
     * @var FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * Content handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Location handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Content type handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Object state handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * Section handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Field registry
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry
     */
    protected $fieldRegistry;

    public function __construct(
        FieldRegistry $fieldRegistry,
        FieldNameGenerator $fieldNameGenerator,
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler
    )
    {
        $this->fieldRegistry = $fieldRegistry;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
    }

    /**
     * Map content to document.
     *
     * A document is an array of fields
     *
     * @param int|string $contentId
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document
     */
    public function mapContentById( $contentId )
    {
        $contentInfo = $this->contentHandler->loadContentInfo( $contentId );

        return $this->mapContent(
            $this->contentHandler->load( $contentId, $contentInfo->currentVersionNo )
        );
    }

    /**
     * Map content to document.
     *
     * A document is an array of fields
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document
     */
    public function mapContent( Content $content )
    {
        $locations = $this->locationHandler->loadLocationsByContent( $content->versionInfo->contentInfo->id );
        $section = $this->sectionHandler->load( $content->versionInfo->contentInfo->sectionId );
        $mainLocation = null;
        $locationDocuments = array();
        foreach ( $locations as $location )
        {
            $locationDocuments[] = $this->mapLocation( $location, $content );
        }

        $fields = array(
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
                array_keys( $content->versionInfo->names ),
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
        );

        $contentType = $this->contentTypeHandler->load( $content->versionInfo->contentInfo->contentTypeId );
        $fields[] = new Field(
            'group',
            $contentType->groupIds,
            new FieldType\MultipleIdentifierField()
        );
        $fields[] = new Field(
            'fields',
            $this->mapFields( $content, $contentType ),
            new FieldType\DocumentField()
        );

        $objectStateIds = array();
        foreach ( $this->objectStateHandler->loadAllGroups() as $objectStateGroup )
        {
            $objectStateIds[] = $this->objectStateHandler->getContentState(
                $content->versionInfo->contentInfo->id,
                $objectStateGroup->id
            )->id;
        }

        $fields[] = new Field(
            'object_state',
            $objectStateIds,
            new FieldType\MultipleIdentifierField()
        );

        $document = new Document(
            array(
                "id" => $content->versionInfo->contentInfo->id,
                "type" => "content",
                "fields" => $fields,
            )
        );

        return $document;
    }

    /**
     *
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document[]
     */
    protected function mapFields( Content $content, Type $contentType )
    {
        $fieldMap = array();

        foreach ( $content->fields as $field )
        {
            $fieldMap[$field->languageCode][] = $field;
        }

        $fieldDocuments = array();

        foreach ( array_keys( $fieldMap ) as $languageCode )
        {
            $fields = array();
            $fields[] = new Field(
                'meta_language_code',
                $languageCode,
                new FieldType\IdentifierField()
            );

            foreach ( $fieldMap[$languageCode] as $field )
            {
                foreach ( $contentType->fieldDefinitions as $fieldDefinition )
                {
                    if ( $fieldDefinition->id !== $field->fieldDefinitionId )
                    {
                        continue;
                    }

                    $fieldType = $this->fieldRegistry->getType( $field->type );
                    foreach ( $fieldType->getIndexData( $field ) as $indexField )
                    {
                        $fields[] = new Field(
                            $this->fieldNameGenerator->getName(
                                $indexField->name,
                                $fieldDefinition->identifier,
                                $contentType->identifier
                            ),
                            $indexField->value,
                            $indexField->type
                        );
                    }
                }
            }

            $fieldDocuments[] = new Document(
                array(
                    "fields" => $fields,
                )
            );
        }

        return $fieldDocuments;
    }

    /**
     * Map content to document.
     *
     * A document is an array of fields
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document
     */
    protected function mapLocation( Location $location, Content $content )
    {
        $fields = array(
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
                'content_id',
                $location->contentId,
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
                ( $location->id == $content->versionInfo->contentInfo->mainLocationId ),
                new FieldType\BooleanField()
            ),
        );

        return new Document(
            array(
                "fields" => $fields
            )
        );
    }

    public function mapContentLocation( Location $location )
    {
        $contentInfo = $this->contentHandler->loadContentInfo( $location->contentId );
        $content = $this->contentHandler->load( $location->contentId, $contentInfo->currentVersionNo );
        $section = $this->sectionHandler->load( $content->versionInfo->contentInfo->sectionId );

        $document = $this->mapLocation( $location, $content );
        $document->id = $location->id;
        $document->type = "location";

        $document->fields[] = new Field(
            'is_main_location',
            ( $location->id == $content->versionInfo->contentInfo->mainLocationId ),
            new FieldType\BooleanField()
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
            array_keys( $content->versionInfo->names ),
            new FieldType\MultipleStringField()
        );
        $document->fields[] = new Field(
            'content_always_available',
            $content->versionInfo->contentInfo->alwaysAvailable,
            new FieldType\BooleanField()
        );

        $contentType = $this->contentTypeHandler->load( $content->versionInfo->contentInfo->contentTypeId );
        $document->fields[] = new Field(
            'content_group',
            $contentType->groupIds,
            new FieldType\MultipleIdentifierField()
        );

        return $document;
    }
}
