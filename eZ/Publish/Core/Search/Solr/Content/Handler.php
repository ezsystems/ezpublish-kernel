<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Search\Content\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\Document;
use eZ\Publish\SPI\Search\FieldType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * The Content Search handler retrieves sets of of Content objects, based on a
 * set of criteria.
 *
 * The basic idea of this class is to do the following:
 *
 * 1) The find methods retrieve a recursive set of filters, which define which
 * content objects to retrieve from the database. Those may be combined using
 * boolean operators.
 *
 * 2) This recursive criterion definition is visited into a query, which limits
 * the content retrieved from the database. We might not be able to create
 * sensible queries from all criterion definitions.
 *
 * 3) The query might be possible to optimize (remove empty statements),
 * reduce singular and and or constructs…
 *
 * 4) Additionally we might need a post-query filtering step, which filters
 * content objects based on criteria, which could not be converted in to
 * database statements.
 */
class Handler implements SearchHandlerInterface
{
    /**
     * Content locator gateway.
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\Gateway
     */
    protected $gateway;

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
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway $gateway
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     */
    public function __construct(
        Gateway $gateway,
        FieldRegistry $fieldRegistry,
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler,
        FieldNameGenerator $fieldNameGenerator
    )
    {
        $this->gateway            = $gateway;
        $this->fieldRegistry      = $fieldRegistry;
        $this->contentHandler = $contentHandler;
        $this->locationHandler    = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler     = $sectionHandler;
        $this->fieldNameGenerator = $fieldNameGenerator;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Query criterion is not applicable to its target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array() )
    {
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        return $this->gateway->findContent( $query, $fieldFilters );
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function findSingle( Criterion $filter, array $fieldFilters = array() )
    {
        $searchQuery = new Query();
        $searchQuery->filter = $filter;
        $searchQuery->query  = new Criterion\MatchAll();
        $searchQuery->offset = 0;
        $searchQuery->limit  = 1;
        $result = $this->findContent( $searchQuery, $fieldFilters );

        if ( !$result->totalCount )
            throw new NotFoundException( 'Content', "findSingle() found no content for given \$filter" );
        else if ( $result->totalCount > 1 )
            throw new InvalidArgumentException( "totalCount", "findSingle() found more then one item for given \$filter" );

        $first = reset( $result->searchHits );
        return $first->valueObject;
    }

    /**
     * Suggests a list of values for the given prefix
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest( $prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null )
    {
        throw new \Exception( "@todo: Not implemented yet." );
    }

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return void
     */
    public function indexContent( Content $content )
    {
        // TODO: maybe not really necessary
        $blockId = "content{$content->versionInfo->contentInfo->id}";
        $this->gateway->deleteBlock( $blockId );

        $document = $this->mapContent( $content );

        $this->gateway->bulkIndexDocuments( array( $document ) );
    }

    /**
     * Indexes several content objects
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content[] $contentObjects
     *
     * @return void
     */
    public function bulkIndexContent( array $contentObjects)
    {
        foreach ( $contentObjects as $content )
            $documents[] = $this->mapContent( $content );

        if ( !empty( $documents ) )
            $this->gateway->bulkIndexDocuments( $documents );
    }

    /**
     * Deletes a content object from the index
     *
     * @param int $contentId
     * @param int|null $versionId
     *
     * @return void
     */
    public function deleteContent( $contentId, $versionId = null )
    {
        $blockId = "content{$contentId}";
        $this->gateway->deleteBlock( $blockId );
    }

    /**
     * Deletes a location from the index
     *
     * @param mixed $locationId
     * @param mixed $contentId
     */
    public function deleteLocation( $locationId, $contentId )
    {
        $blockId = "content{$contentId}";
        $this->gateway->deleteBlock( $blockId );

        // TODO it seems this part of location deletion (not last location) misses integration tests
        try
        {
            $contentInfo = $this->contentHandler->loadContentInfo( $contentId );
        }
        catch ( NotFoundException $e )
        {
            return;
        }

        $content = $this->contentHandler->load( $contentId, $contentInfo->currentVersionNo );
        $this->bulkIndexContent( array( $content ) );
    }

    /**
     * Map content to document.
     *
     * A document is an array of fields
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return array
     */
    protected function mapContent( Content $content )
    {
        $locations = $this->locationHandler->loadLocationsByContent( $content->versionInfo->contentInfo->id );
        $section = $this->sectionHandler->load( $content->versionInfo->contentInfo->sectionId );
        $mainLocation = null;
        $isSomeLocationVisible = false;
        $locationDocuments = array();
        foreach ( $locations as $location )
        {
            $locationDocuments[] = $this->mapLocation( $location, $content, $section );

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
                'id',
                'content' . $content->versionInfo->contentInfo->id,
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
                'some_location_visible',
                $isSomeLocationVisible,
                new FieldType\BooleanField()
            ),
        );

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
                        continue;

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
                "fields" => $fields,
                "documents" => $locationDocuments,
            )
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
     * Map content to document.
     *
     * A document is an array of fields
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Section $section
     *
     * @return array
     */
    protected function mapLocation( Location $location, Content $content, Section $section )
    {
        $fields = array(
            new Field(
                'id',
                'location' . $location->id,
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
            // Note: denormalized Content data is prefixed with 'content_' to avoid
            // conflicts when using parent filter
            new Field(
                'content_name',
                $content->versionInfo->contentInfo->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_section_identifier',
                $section->identifier,
                new FieldType\IdentifierField()
            ),
            new Field(
                'content_section_name',
                $section->name,
                new FieldType\StringField()
            ),
            new Field(
                'content_modified',
                $content->versionInfo->contentInfo->modificationDate,
                new FieldType\DateField()
            ),
            new Field(
                'content_published',
                $content->versionInfo->contentInfo->publicationDate,
                new FieldType\DateField()
            ),
        );

        return new Document(
            array(
                "fields" => $fields
            )
        );
    }

    /**
     * Purges all contents from the index
     *
     * @todo: Make this public API?
     *
     * @return void
     */
    public function purgeIndex()
    {
        $this->gateway->purgeIndex();
    }

    /**
     * Set if index/delete actions should commit or if several actions is to be expected
     *
     * This should be set to false before group of actions and true before the last one
     *
     * @param bool $commit
     */
    public function setCommit( $commit )
    {
        $this->gateway->setCommit( $commit );
    }
}

