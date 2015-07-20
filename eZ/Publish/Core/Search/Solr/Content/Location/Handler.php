<?php

/**
 * File containing the Location Search handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Location;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Content\Location\Handler as SearchHandlerInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Search\Common\FieldNameGenerator;
use eZ\Publish\Core\Search\Solr\Content\DocumentMapper;
use eZ\Publish\Core\Search\Solr\Content\ResultExtractor;
use eZ\Publish\Core\Search\Solr\Content\Gateway;

/**
 *
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
     * Field name generator.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $fieldNameGenerator;

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
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $fieldNameGenerator
     * @param \eZ\Publish\Core\Search\Solr\Content\DocumentMapper
     * @param \eZ\Publish\Core\Search\Solr\Content\ResultExtractor $resultExtractor
     */
    public function __construct(
        Gateway $gateway,
        FieldNameGenerator $fieldNameGenerator,
        DocumentMapper $mapper,
        ResultExtractor $resultExtractor
    ) {
        $this->gateway = $gateway;
        $this->fieldNameGenerator = $fieldNameGenerator;
        $this->mapper = $mapper;
        $this->resultExtractor = $resultExtractor;
    }

    /**
     * Finds Locations for the given $query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations(LocationQuery $query, array $fieldFilters = array())
    {
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        return $this->resultExtractor->extract(
            $this->gateway->find($query, $fieldFilters)
        );
    }

    /**
     * Indexes a Location in the index storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation(Location $location)
    {
        $this->bulkIndexLocations(array($location));
    }

    /**
     * Indexes several content objects.
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location[] $locations
     */
    public function bulkIndexLocations(array $locations)
    {
        $documents = array();

        foreach ($locations as $location) {
            $documents[] = $this->mapper->mapLocation($location);
        }

        if (!empty($documents)) {
            $this->gateway->bulkIndexDocuments($documents);
        }
    }

    /**
     * Deletes a Location from the index storage.
     *
     * @param int|string $locationId
     */
    public function deleteLocation($locationId)
    {
        $this->gateway->deleteByQuery("location_id:{$locationId}");
    }

    /**
     * Deletes a Content from the index storage.
     *
     * @param int|string $contentId
     */
    public function deleteContent($contentId)
    {
        $this->gateway->deleteByQuery("content_id_id:{$contentId}");
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
