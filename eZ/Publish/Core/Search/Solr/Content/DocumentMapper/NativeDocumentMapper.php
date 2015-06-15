<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\DocumentMapper;

use eZ\Publish\Core\Search\Solr\Content\DocumentMapper;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;

/**
 * NativeDocumentMapper maps Solr backend documents per Content translation
 */
class NativeDocumentMapper implements DocumentMapper
{
    /**
     * Field registry
     *
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

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
     * Field name generator
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        FieldRegistry $fieldRegistry,
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler,
        FieldNameGenerator $fieldNameGenerator
    )
    {
        $this->fieldRegistry = $fieldRegistry;
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler = $sectionHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Search\Document[]
     */
    public function mapContent( Content $content )
    {
        $locations = $this->locationHandler->loadLocationsByContent( $content->versionInfo->contentInfo->id );
        $section = $this->sectionHandler->load( $content->versionInfo->contentInfo->sectionId );
        $mainLocation = null;
        $isSomeLocationVisible = false;
        $locationData = array();

        foreach ( $locations as $location )
        {
            $locationData["ids"][] = $location->id;
            $locationData["parent_ids"][] = $location->parentId;
            $locationData["remote_ids"][] = $location->remoteId;
            $locationData["path_strings"][] = $location->pathString;
            $locationData["depths"][] = $location->depth;

            if ( $location->id == $content->versionInfo->contentInfo->mainLocationId )
            {
                $mainLocation = $location;
            }

            if ( !$location->hidden && !$location->invisible )
            {
                $isSomeLocationVisible = true;
            }
        }

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds(
            $content->versionInfo->contentInfo->ownerId
        );
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $content->versionInfo->contentInfo->ownerId;

        $fields = array(
            new Field(
                'content',
                $content->versionInfo->contentInfo->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'document_type',
                'content',
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
                array_keys( $content->versionInfo->names ),
                new FieldType\MultipleStringField()
            ),
            new Field(
                'main_language_code',
                $content->versionInfo->contentInfo->mainLanguageCode,
                new FieldType\StringField()
            ),
            new Field(
                'always_available',
                $content->versionInfo->contentInfo->alwaysAvailable,
                new FieldType\BooleanField()
            ),
            new Field(
                'location_visible',
                $isSomeLocationVisible,
                new FieldType\BooleanField()
            ),
        );

        if ( !empty( $locationData ) )
        {
            $fields[] = new Field(
                'location_id',
                $locationData["ids"],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_parent_id',
                $locationData["parent_ids"],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_remote_id',
                $locationData["remote_ids"],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_path_string',
                $locationData["path_strings"],
                new FieldType\MultipleIdentifierField()
            );
            $fields[] = new Field(
                'location_depth',
                $locationData["depths"],
                new FieldType\MultipleIntegerField()
            );
        }

        if ( $mainLocation !== null )
        {
            $fields[] = new Field(
                'main_location',
                $mainLocation->id,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_parent',
                $mainLocation->parentId,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_remote_id',
                $mainLocation->remoteId,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_visible',
                !$mainLocation->hidden && !$mainLocation->invisible,
                new FieldType\BooleanField()
            );
            $fields[] = new Field(
                'main_location_path',
                $mainLocation->pathString,
                new FieldType\IdentifierField()
            );
            $fields[] = new Field(
                'main_location_depth',
                $mainLocation->depth,
                new FieldType\IntegerField()
            );
            $fields[] = new Field(
                'main_location_priority',
                $mainLocation->priority,
                new FieldType\IntegerField()
            );
        }

        $contentType = $this->contentTypeHandler->load( $content->versionInfo->contentInfo->contentTypeId );
        $fields[] = new Field(
            'group',
            $contentType->groupIds,
            new FieldType\MultipleIdentifierField()
        );

        $fields[] = new Field(
            'object_state',
            $this->getObjectStateIds( $content->versionInfo->contentInfo->id ),
            new FieldType\MultipleIdentifierField()
        );

        $fieldSets = $this->mapContentFields( $content, $contentType );
        $documents = array();

        foreach ( $fieldSets as $languageCode => $translationFields )
        {
            $translationFields[] = new Field(
                'id',
                $this->generateContentDocumentId( $content, $languageCode ),
                new FieldType\IdentifierField()
            );
            $translationFields[] = new Field(
                'meta_indexed_language_code',
                $languageCode,
                new FieldType\StringField()
            );
            $translationFields[] = new Field(
                'meta_indexed_is_main_translation',
                ( $languageCode === $content->versionInfo->contentInfo->mainLanguageCode ),
                new FieldType\BooleanField()
            );
            $translationFields[] = new Field(
                'meta_indexed_is_main_translation_and_always_available',
                (
                    ( $languageCode === $content->versionInfo->contentInfo->mainLanguageCode ) &&
                    $content->versionInfo->contentInfo->alwaysAvailable
                ),
                new FieldType\BooleanField()
            );

            $documents[] = new Document(
                array(
                    "languageCode" => $languageCode,
                    "fields" => array_merge( $fields, $translationFields ),
                )
            );
        }

        return $documents;
    }

    /**
     * Generates the Solr backend document id for Content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param string $languageCode
     *
     * @return string
     */
    protected function generateContentDocumentId( Content $content, $languageCode )
    {
        return strtolower( "content{$content->versionInfo->contentInfo->id}{$languageCode}" );
    }

    /**
     * Generates the Solr backend document id for Content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     * @param string $languageCode
     *
     * @return string
     */
    protected function generateLocationDocumentId( Location $location, $languageCode )
    {
        return strtolower( "location{$location->id}{$languageCode}" );
    }

    /**
     * Maps given Content to a Document.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     *
     * @return \eZ\Publish\SPI\Search\Field[][]
     */
    protected function mapContentFields( Content $content, ContentType $contentType )
    {
        $fieldSets = array();

        foreach ( $content->fields as $field )
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
                    if ( $indexField->value === null )
                    {
                        continue;
                    }

                    $fieldSets[$field->languageCode][] = new Field(
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

        return $fieldSets;
    }

    public function mapLocation( Location $location )
    {
        $contentInfo = $this->contentHandler->loadContentInfo( $location->contentId );
        $content = $this->contentHandler->load( $location->contentId, $contentInfo->currentVersionNo );
        $section = $this->sectionHandler->load( $content->versionInfo->contentInfo->sectionId );

        $fields = array(
            new Field(
                'location',
                $location->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'document_type',
                'location',
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
                ( $location->id == $content->versionInfo->contentInfo->mainLocationId ),
                new FieldType\BooleanField()
            ),
        );

        // UserGroups and Users are Content, but permissions cascade is achieved through
        // Locations hierarchy. We index all ancestor Location Content ids of all
        // Locations of an owner.
        $ancestorLocationsContentIds = $this->getAncestorLocationsContentIds( $contentInfo->ownerId );
        // Add owner user id as it can also be considered as user group.
        $ancestorLocationsContentIds[] = $contentInfo->ownerId;
        $fields[] = new Field(
            'content_owner_user_group',
            $ancestorLocationsContentIds,
            new FieldType\MultipleIdentifierField()
        );

        $fields[] = new Field(
            'content_id',
            $content->versionInfo->contentInfo->id,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_type',
            $content->versionInfo->contentInfo->contentTypeId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_version',
            $content->versionInfo->versionNo,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_status',
            $content->versionInfo->status,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_name',
            $content->versionInfo->contentInfo->name,
            new FieldType\StringField()
        );
        $fields[] = new Field(
            'content_creator',
            $content->versionInfo->creatorId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_owner',
            $content->versionInfo->contentInfo->ownerId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_section',
            $content->versionInfo->contentInfo->sectionId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_section_identifier',
            $section->identifier,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_section_name',
            $section->name,
            new FieldType\StringField()
        );
        $fields[] = new Field(
            'content_remote_id',
            $content->versionInfo->contentInfo->remoteId,
            new FieldType\IdentifierField()
        );
        $fields[] = new Field(
            'content_modified',
            $content->versionInfo->contentInfo->modificationDate,
            new FieldType\DateField()
        );
        $fields[] = new Field(
            'content_published',
            $content->versionInfo->contentInfo->publicationDate,
            new FieldType\DateField()
        );
        $fields[] = new Field(
            'content_language_code',
            array_keys( $content->versionInfo->names ),
            new FieldType\MultipleStringField()
        );
        $fields[] = new Field(
            'content_always_available',
            $content->versionInfo->contentInfo->alwaysAvailable,
            new FieldType\BooleanField()
        );
        $fields[] = new Field(
            'content_group',
            $this->contentTypeHandler->load(
                $content->versionInfo->contentInfo->contentTypeId
            )->groupIds,
            new FieldType\MultipleIdentifierField()
        );
        $fields[] = new Field(
            'content_object_state',
            $this->getObjectStateIds( $content->versionInfo->contentInfo->id ),
            new FieldType\MultipleIdentifierField()
        );

        $contentType = $this->contentTypeHandler->load(
            $content->versionInfo->contentInfo->contentTypeId
        );
        $fieldSets = $this->mapContentFields( $content, $contentType );
        $documents = array();

        foreach ( $fieldSets as $languageCode => $translationFields )
        {
            $translationFields[] = new Field(
                'id',
                $this->generateLocationDocumentId( $location, $languageCode ),
                new FieldType\IdentifierField()
            );
            $translationFields[] = new Field(
                'meta_indexed_language_code',
                $languageCode,
                new FieldType\StringField()
            );
            $translationFields[] = new Field(
                'meta_indexed_is_main_translation',
                ( $languageCode === $content->versionInfo->contentInfo->mainLanguageCode ),
                new FieldType\BooleanField()
            );
            $translationFields[] = new Field(
                'meta_indexed_is_main_translation_and_always_available',
                (
                    ( $languageCode === $content->versionInfo->contentInfo->mainLanguageCode ) &&
                    $content->versionInfo->contentInfo->alwaysAvailable
                ),
                new FieldType\BooleanField()
            );

            $documents[] = new Document(
                array(
                    "languageCode" => $languageCode,
                    "fields" => array_merge( $fields, $translationFields ),
                )
            );
        }

        return $documents;
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
    protected function getAncestorLocationsContentIds( $contentId )
    {
        $locations = $this->locationHandler->loadLocationsByContent( $contentId );
        $ancestorLocationContentIds = array();
        $ancestorLocationIds = array();

        foreach ( $locations as $location )
        {
            $locationIds = explode( "/", trim( $location->pathString, "/" ) );
            // Remove Location of Content with $contentId
            array_pop( $locationIds );
            // Remove Root Location id (id==1 in legacy DB)
            array_shift( $locationIds );

            $ancestorLocationIds = array_merge( $ancestorLocationIds, $locationIds );
        }

        foreach ( array_unique( $ancestorLocationIds ) as $locationId )
        {
            $location = $this->locationHandler->load( $locationId );

            $ancestorLocationContentIds[$location->contentId] = true;
        }

        return array_keys( $ancestorLocationContentIds );
    }

    /**
     * Returns an array of object state ids of a Content with given $contentId.
     *
     * @param int|string $contentId
     *
     * @return array
     */
    protected function getObjectStateIds( $contentId )
    {
        $objectStateIds = array();

        foreach ( $this->objectStateHandler->loadAllGroups() as $objectStateGroup )
        {
            $objectStateIds[] = $this->objectStateHandler->getContentState(
                $contentId,
                $objectStateGroup->id
            )->id;
        }

        return $objectStateIds;
    }
}
