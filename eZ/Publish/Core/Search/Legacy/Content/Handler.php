<?php

/**
 * File containing the Content Search handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Handler as SearchHandlerInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

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
     * @var \eZ\Publish\Core\Search\Legacy\Content\Gateway
     */
    protected $gateway;

    /**
     * Location locator gateway.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Content mapper.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Location locationMapper.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Creates a new content handler.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Gateway $gateway
     * @param \eZ\Publish\Core\Search\Legacy\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $contentMapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper $locationMapper
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(
        Gateway $gateway,
        LocationGateway $locationGateway,
        ContentMapper $contentMapper,
        LocationMapper $locationMapper,
        LanguageHandler $languageHandler
    ) {
        $this->gateway = $gateway;
        $this->locationGateway = $locationGateway;
        $this->contentMapper = $contentMapper;
        $this->locationMapper = $locationMapper;
        $this->languageHandler = $languageHandler;
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
    public function findContent(Query $query, array $languageFilter = array())
    {
        if (!isset($languageFilter['languages'])) {
            $languageFilter['languages'] = array();
        }

        if (!isset($languageFilter['useAlwaysAvailable'])) {
            $languageFilter['useAlwaysAvailable'] = true;
        }

        $start = microtime(true);
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        if (count($query->facetBuilders)) {
            throw new NotImplementedException('Facets are not supported by the legacy search engine.');
        }

        // The legacy search does not know about scores, so that we just
        // combine the query with the filter
        $filter = new Criterion\LogicalAnd(array($query->query, $query->filter));

        $data = $this->gateway->find(
            $filter,
            $query->offset,
            $query->limit,
            $query->sortClauses,
            $languageFilter,
            $query->performCount
        );

        $result = new SearchResult();
        $result->time = microtime(true) - $start;
        $result->totalCount = $data['count'];
        $contentInfoList = $this->contentMapper->extractContentInfoFromRows(
            $data['rows'],
            '',
            'main_tree_'
        );

        foreach ($contentInfoList as $index => $contentInfo) {
            $searchHit = new SearchHit();
            $searchHit->valueObject = $contentInfo;
            $searchHit->matchedTranslation = $this->extractMatchedLanguage(
                $data['rows'][$index]['language_mask'],
                $data['rows'][$index]['initial_language_id'],
                $languageFilter
            );

            $result->searchHits[] = $searchHit;
        }

        return $result;
    }

    protected function extractMatchedLanguage($languageMask, $mainLanguageId, $languageSettings)
    {
        foreach ($languageSettings['languages'] as $languageCode) {
            if ($languageMask & $this->languageHandler->loadByLanguageCode($languageCode)->id) {
                return $languageCode;
            }
        }

        if ($languageMask & 1 || empty($languageSettings['languages'])) {
            return $this->languageHandler->load($mainLanguageId)->languageCode;
        }

        return null;
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
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function findSingle(Criterion $filter, array $languageFilter = array())
    {
        if (!isset($languageFilter['languages'])) {
            $languageFilter['languages'] = array();
        }

        if (!isset($languageFilter['useAlwaysAvailable'])) {
            $languageFilter['useAlwaysAvailable'] = true;
        }

        $searchQuery = new Query();
        $searchQuery->filter = $filter;
        $searchQuery->query = new Criterion\MatchAll();
        $searchQuery->offset = 0;
        $searchQuery->limit = 2;// Because we optimize away the count query below
        $searchQuery->performCount = true;
        $searchQuery->sortClauses = null;
        $result = $this->findContent($searchQuery, $languageFilter);

        if (empty($result->searchHits)) {
            throw new NotFoundException('Content', 'findSingle() found no content for given $criterion');
        } elseif (isset($result->searchHits[1])) {
            throw new InvalidArgumentException('totalCount', 'findSingle() found more then one item for given $criterion');
        }

        $first = reset($result->searchHits);

        return $first->valueObject;
    }

    /**
     * @see \eZ\Publish\SPI\Search\Handler::findLocations
     */
    public function findLocations(LocationQuery $query, array $languageFilter = array())
    {
        if (!isset($languageFilter['languages'])) {
            $languageFilter['languages'] = array();
        }

        if (!isset($languageFilter['useAlwaysAvailable'])) {
            $languageFilter['useAlwaysAvailable'] = true;
        }

        $start = microtime(true);
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        if (count($query->facetBuilders)) {
            throw new NotImplementedException('Facets are not supported by the legacy search engine.');
        }

        // The legacy search does not know about scores, so we just
        // combine the query with the filter
        $data = $this->locationGateway->find(
            new Criterion\LogicalAnd(array($query->query, $query->filter)),
            $query->offset,
            $query->limit,
            $query->sortClauses,
            $languageFilter,
            $query->performCount
        );

        $result = new SearchResult();
        $result->time = microtime(true) - $start;
        $result->totalCount = $data['count'];
        $locationList = $this->locationMapper->createLocationsFromRows($data['rows']);

        foreach ($locationList as $index => $location) {
            $searchHit = new SearchHit();
            $searchHit->valueObject = $location;
            $searchHit->matchedTranslation = $this->extractMatchedLanguage(
                $data['rows'][$index]['language_mask'],
                $data['rows'][$index]['initial_language_id'],
                $languageFilter
            );

            $result->searchHits[] = $searchHit;
        }

        return $result;
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
        throw new NotImplementedException('Suggestions are not supported by legacy search engine.');
    }

    /**
     * Indexes a content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent(Content $content)
    {
        // Not implemented in Legacy Storage Engine
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation(Location $location)
    {
        // Not implemented in Legacy Storage Engine
    }

    /**
     * Deletes a content object from the index.
     *
     * @param int $contentId
     * @param int|null $versionId
     */
    public function deleteContent($contentId, $versionId = null)
    {
        // Not implemented in Legacy Storage Engine
    }

    /**
     * Deletes a location from the index.
     *
     * @param mixed $locationId
     * @param mixed $contentId
     */
    public function deleteLocation($locationId, $contentId)
    {
        // Not implemented in Legacy Storage Engine
    }
}
