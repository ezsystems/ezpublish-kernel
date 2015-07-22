<?php

/**
 * File containing the Content Search handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Search\Content\Handler as SearchHandlerInterface;
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
     * @var \eZ\Publish\Core\Search\Solr\Content\Gateway
     */
    protected $gateway;

    /**
     * Content handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Document mapper.
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\DocumentMapper
     */
    protected $mapper;

    /**
     * Result extractor.
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\ResultExtractor
     */
    protected $resultExtractor;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway $gateway
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\Core\Search\Solr\Content\DocumentMapper $mapper
     * @param \eZ\Publish\Core\Search\Solr\Content\ResultExtractor $resultExtractor
     */
    public function __construct(
        Gateway $gateway,
        ContentHandler $contentHandler,
        DocumentMapper $mapper,
        ResultExtractor $resultExtractor
    ) {
        $this->gateway = $gateway;
        $this->contentHandler = $contentHandler;
        $this->mapper = $mapper;
        $this->resultExtractor = $resultExtractor;
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
    public function findContent(Query $query, array $fieldFilters = array())
    {
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        return $this->resultExtractor->extract(
            $this->gateway->find($query, $fieldFilters)
        );
    }

    /**
     * Performs a query for a single content object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function findSingle(Criterion $filter, array $fieldFilters = array())
    {
        $searchQuery = new Query();
        $searchQuery->filter = $filter;
        $searchQuery->query = new Criterion\MatchAll();
        $searchQuery->offset = 0;
        $searchQuery->limit = 1;
        $result = $this->findContent($searchQuery, $fieldFilters);

        if (!$result->totalCount) {
            throw new NotFoundException('Content', "findSingle() found no content for given \$filter");
        } elseif ($result->totalCount > 1) {
            throw new InvalidArgumentException('totalCount', "findSingle() found more then one item for given \$filter");
        }

        $first = reset($result->searchHits);

        return $first->valueObject;
    }

    /**
     * Suggests a list of values for the given prefix.
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest($prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null)
    {
        throw new \Exception('@todo: Not implemented yet.');
    }

    /**
     * Indexes a content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent(Content $content)
    {
        $this->gateway->bulkIndexDocuments(array($this->mapper->mapContent($content)));
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
        $documents = array();

        foreach ($contentObjects as $content) {
            $documents[] = $this->mapper->mapContent($content);
        }

        if (!empty($documents)) {
            $this->gateway->bulkIndexDocuments($documents);
        }
    }

    /**
     * Deletes a content object from the index.
     *
     * @param int $contentId
     * @param int|null $versionId
     */
    public function deleteContent($contentId, $versionId = null)
    {
        $this->gateway->deleteByQuery("content_id:{$contentId}");
    }

    /**
     * Deletes a location from the index.
     *
     * @param mixed $locationId
     * @param mixed $contentId
     */
    public function deleteLocation($locationId, $contentId)
    {
        $this->gateway->deleteByQuery("content_id:{$contentId}");

        // TODO it seems this part of location deletion (not last location) misses integration tests
        try {
            $contentInfo = $this->contentHandler->loadContentInfo($contentId);
        } catch (NotFoundException $e) {
            return;
        }

        $content = $this->contentHandler->load($contentId, $contentInfo->currentVersionNo);
        $this->bulkIndexContent(array($content));
    }

    /**
     * Purges all contents from the index.
     *
     * @todo: Make this public API?
     */
    public function purgeIndex()
    {
        $this->gateway->purgeIndex();
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
        $this->gateway->setCommit($commit);
    }
}
