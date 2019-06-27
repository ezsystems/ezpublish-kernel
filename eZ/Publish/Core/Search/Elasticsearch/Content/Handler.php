<?php

/**
 * File containing the Content Search handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Handler as SearchHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * ElasticSearch Handler.
 *
 * @deprecated
 */
class Handler implements SearchHandlerInterface
{
    /** @var \eZ\Publish\Core\Search\Elasticsearch\Content\Gateway */
    protected $gateway;

    /** @var \eZ\Publish\Core\Search\Elasticsearch\Content\Gateway */
    protected $locationGateway;

    /** @var \eZ\Publish\Core\Search\Elasticsearch\Content\MapperInterface */
    protected $mapper;

    /**
     * Search result extractor.
     *
     * @var \eZ\Publish\Core\Search\Elasticsearch\Content\Extractor
     */
    protected $extractor;

    /**
     * Identifier of Content document type in the search backend.
     *
     * @var string
     */
    protected $contentDocumentTypeIdentifier;

    /**
     * Identifier of Location document type in the search backend.
     *
     * @var string
     */
    protected $locationDocumentTypeIdentifier;

    public function __construct(
        Gateway $gateway,
        Gateway $locationGateway,
        MapperInterface $mapper,
        Extractor $extractor,
        $contentDocumentTypeIdentifier,
        $locationDocumentTypeIdentifier
    ) {
        $this->gateway = $gateway;
        $this->locationGateway = $locationGateway;
        $this->mapper = $mapper;
        $this->extractor = $extractor;
        $this->contentDocumentTypeIdentifier = $contentDocumentTypeIdentifier;
        $this->locationDocumentTypeIdentifier = $locationDocumentTypeIdentifier;
    }

    /**
     * Finds content objects for the given query.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Query criterion is not applicable to its target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageFilter - a map of language related filters specifying languages query will be performed on.
     *        Also used to define which field languages are loaded for the returned content.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent(Query $query, array $languageFilter = [])
    {
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        $data = $this->gateway->find($query, $this->contentDocumentTypeIdentifier, $languageFilter);

        return $this->extractor->extract($data);
    }

    /**
     * Performs a query for a single content object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $languageFilter - a map of language related filters specifying languages query will be performed on.
     *        Also used to define which field languages are loaded for the returned content.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function findSingle(Criterion $filter, array $languageFilter = [])
    {
        $query = new Query();
        $query->filter = $filter;
        $query->offset = 0;
        $query->limit = 1;
        $result = $this->findContent($query, $languageFilter);

        if (!$result->totalCount) {
            throw new NotFoundException(
                'Content',
                'findSingle() found no content for given $filter'
            );
        } elseif ($result->totalCount > 1) {
            throw new InvalidArgumentException(
                'totalCount',
                'findSingle() found more then one item for given $filter'
            );
        }

        return $result->searchHits[0]->valueObject;
    }

    /**
     * Finds Locations for the given $query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $languageFilter - a map of language related filters specifying languages query will be performed on.
     *        Also used to define which field languages are loaded for the returned content.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations(LocationQuery $query, array $languageFilter = [])
    {
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        $data = $this->locationGateway->find($query, $this->locationDocumentTypeIdentifier);

        return $this->extractor->extract($data);
    }

    /**
     * Suggests a list of values for the given prefix.
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null)
    {
        // TODO: Implement suggest() method.
    }

    /**
     * Indexes a content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent(Content $content)
    {
        $document = $this->mapper->mapContent($content);

        $this->gateway->index($document);
    }

    /**
     * Indexes several content objects.
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content[] $contentObjects
     */
    public function bulkIndexContent(array $contentObjects)
    {
        $documents = [];
        foreach ($contentObjects as $content) {
            $documents[] = $this->mapper->mapContent($content);
        }

        $this->gateway->bulkIndex($documents);
    }

    /**
     * Indexes a Location in the index storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation(Location $location)
    {
        $document = $this->mapper->mapLocation($location);

        $this->gateway->index($document);
    }

    /**
     * Indexes several Locations.
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location[] $locations
     */
    public function bulkIndexLocations(array $locations)
    {
        $documents = [];
        foreach ($locations as $location) {
            $documents[] = $this->mapper->mapLocation($location);
        }

        $this->gateway->bulkIndex($documents);
    }

    /**
     * Deletes a content object from the index.
     *
     * @param int $contentId
     * @param int|null $versionId
     */
    public function deleteContent($contentId, $versionId = null)
    {
        // 1. Delete the Content
        if ($versionId === null) {
            $this->gateway->deleteByQuery(json_encode(['query' => ['match' => ['_id' => $contentId]]]), $this->contentDocumentTypeIdentifier);
        } else {
            $this->gateway->delete($contentId, $this->contentDocumentTypeIdentifier);
        }

        // 2. Delete all Content's Locations
        $this->gateway->deleteByQuery(json_encode(['query' => ['match' => ['content_id_id' => $contentId]]]), $this->locationDocumentTypeIdentifier);
    }

    /**
     * Deletes a location from the index.
     *
     * @todo When we support Location-less Content, we will have to reindex instead of removing
     * @todo Should we not already support the above?
     * @todo The subtree could potentially be huge, so this implementation should scroll reindex
     *
     * @param mixed $locationId
     * @param mixed $contentId @todo Make use of this, or remove if not needed.
     */
    public function deleteLocation($locationId, $contentId)
    {
        // 1. Update (reindex) all Content in the subtree with additional Location(s) outside of it
        $ast = [
            'filter' => [
                'and' => [
                    0 => [
                        'nested' => [
                            'path' => 'locations_doc',
                            'filter' => [
                                'regexp' => [
                                    'locations_doc.path_string_id' => ".*/{$locationId}/.*",
                                ],
                            ],
                        ],
                    ],
                    1 => [
                        'nested' => [
                            'path' => 'locations_doc',
                            'filter' => [
                                'regexp' => [
                                    'locations_doc.path_string_id' => [
                                        // Matches anything (@) and (&) not (~) <expression>
                                        'value' => "@&~(.*/{$locationId}/.*)",
                                        'flags' => 'INTERSECTION|COMPLEMENT|ANYSTRING',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->gateway->findRaw(json_encode($ast), $this->contentDocumentTypeIdentifier);
        $result = json_decode($response->body);

        $documents = [];
        foreach ($result->hits->hits as $hit) {
            $documents[] = $this->mapper->mapContentById($hit->_id);
        }

        $this->gateway->bulkIndex($documents);

        // 2. Delete all Content in the subtree with no other Location(s) outside of it
        $ast['filter']['and'][1] = [
            'not' => $ast['filter']['and'][1],
        ];
        $ast = [
            'query' => [
                'filtered' => $ast,
            ],
        ];

        $response = $this->gateway->findRaw(json_encode($ast), $this->contentDocumentTypeIdentifier);
        $documentsToDelete = json_decode($response->body);

        foreach ($documentsToDelete->hits->hits as $documentToDelete) {
            $this->gateway->deleteByQuery(json_encode(['query' => ['match' => ['_id' => $documentToDelete->_id]]]), $this->contentDocumentTypeIdentifier);
            $this->gateway->deleteByQuery(json_encode(['query' => ['match' => ['content_id_id' => $documentToDelete->_id]]]), $this->locationDocumentTypeIdentifier);
        }
    }

    /**
     * Purges all contents from the index.
     */
    public function purgeIndex()
    {
        $this->gateway->purgeIndex($this->contentDocumentTypeIdentifier);
        $this->gateway->purgeIndex($this->locationDocumentTypeIdentifier);
    }

    /**
     * Set if index/delete actions should commit or if several actions is to be expected.
     *
     * This should be set to false before group of actions and true before the last one
     *
     * @param bool $commit
     */
    public function setCommit($commit)
    {
        //$this->gateway->setCommit( $commit );
    }

    public function flush()
    {
        $this->gateway->flush();
    }
}
