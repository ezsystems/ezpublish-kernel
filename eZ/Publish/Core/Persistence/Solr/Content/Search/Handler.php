<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\Search\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SectionHandler;
use eZ\Publish\SPI\Persistence\Content\Search\Field;
use eZ\Publish\SPI\Persistence\Content\Search\FieldType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
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
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway
     */
    protected $gateway;

    /**
     * Field registry
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry
     */
    protected $fieldRegistry;

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
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Section\Handler $sectionHandler
     */
    public function __construct(
        Gateway $gateway,
        FieldRegistry $fieldRegistry,
        LocationHandler $locationHandler,
        ContentTypeHandler $contentTypeHandler,
        ObjectStateHandler $objectStateHandler,
        SectionHandler $sectionHandler
    )
    {
        $this->gateway            = $gateway;
        $this->fieldRegistry      = $fieldRegistry;
        $this->locationHandler    = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->objectStateHandler = $objectStateHandler;
        $this->sectionHandler     = $sectionHandler;
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
        $document = $this->mapContent( $content );
        $this->gateway->bulkIndexContent( array( $document ) );
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
            $this->gateway->bulkIndexContent( $documents );
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
        $this->gateway->deleteContent( $contentId, $versionId );
    }

    /**
     * Deletes a location from the index
     *
     * @param mixed $locationId
     */
    public function deleteLocation( $locationId )
    {
        $this->gateway->deleteLocation( $locationId );
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
        $mainLocation = null;
        foreach ( $locations as $location )
        {
            if ( $location->id == $content->versionInfo->contentInfo->mainLocationId )
                $mainLocation = $location;
        }
        $section = $this->sectionHandler->load( $content->versionInfo->contentInfo->sectionId );

        $document = array(
            new Field(
                'id',
                $content->versionInfo->contentInfo->id,
                new FieldType\IdentifierField()
            ),
            new Field(
                'ez_content',
                // @TODO: I would prefer to use the content object stripping,
                // which is implemented for the fields in the SqlNG branch, but
                // since is not merged I do not want to dublicate the effort.
                base64_encode( serialize( $content ) ),
                new FieldType\BinaryField()
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
                'path',
                array_map(
                    function ( $location )
                    {
                        return $location->pathString;
                    },
                    $locations
                ),
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'location',
                array_map(
                    function ( $location )
                    {
                        return $location->id;
                    },
                    $locations
                ),
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'depth',
                array_map(
                    function ( $location )
                    {
                        return $location->depth;
                    },
                    $locations
                ),
                new FieldType\IntegerField()
            ),
            new Field(
                'priority',
                array_map(
                    function ( $location )
                    {
                        return $location->priority;
                    },
                    $locations
                ),
                new FieldType\IntegerField()
            ),
            new Field(
                'location_parent',
                array_map(
                    function ( $location )
                    {
                        return $location->parentId;
                    },
                    $locations
                ),
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'location_remote_id',
                array_map(
                    function ( $location )
                    {
                        return $location->remoteId;
                    },
                    $locations
                ),
                new FieldType\MultipleIdentifierField()
            ),
            new Field(
                'language_code',
                array_keys( $content->versionInfo->names ),
                new FieldType\MultipleStringField()
            ),
            new Field(
                'invisible',
                array_map(
                    function ( $location )
                    {
                        return $location->invisible;
                    },
                    $locations
                ),
                new FieldType\MultipleBooleanField()
            ),
        );

        if ( $mainLocation !== null )
        {
            $document[] = new Field(
                'main_location',
                $mainLocation->id,
                new FieldType\IdentifierField()
            );
            $document[] = new Field(
                'main_location_parent',
                $mainLocation->parentId,
                new FieldType\IdentifierField()
            );
            $document[] = new Field(
                'main_location_remote_id',
                $mainLocation->remoteId,
                new FieldType\IdentifierField()
            );
            $document[] = new Field(
                'main_path',
                $mainLocation->pathString,
                new FieldType\IdentifierField()
            );
            $document[] = new Field(
                'main_depth',
                $mainLocation->depth,
                new FieldType\IntegerField()
            );
            $document[] = new Field(
                'main_priority',
                $mainLocation->priority,
                new FieldType\IntegerField()
            );
        }

        $contentType = $this->contentTypeHandler->load( $content->versionInfo->contentInfo->contentTypeId );
        $document[] = new Field(
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
                $prefix    = $contentType->identifier . '/' . $fieldDefinition->identifier . '/';
                foreach ( $fieldType->getIndexData( $field ) as $indexField )
                {
                    $document[] = new Field( $prefix . $indexField->name, $indexField->value, $indexField->type );
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

        $document[] = new Field(
            'object_state',
            $objectStateIds,
            new FieldType\MultipleIdentifierField()
        );

        return $document;
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

