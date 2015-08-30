<?php
/**
 * File containing a Search handler implementation for stop watch taking.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Handler as SearchHandlerInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * A decorator of {@see SearchHandlerInterface} for tracking stop watch information.
 */
class StopWatchHandler implements SearchHandlerInterface
{
    /**
     * @var SearchHandlerInterface
     */
    protected $handler;

    /**
     * @var null|Stopwatch
     */
    protected $stopwatch;

    /**
     * Constructs the search stop watch class.
     *
     * @param null|Stopwatch $stopwatch
     */
    public function __construct(SearchHandlerInterface $handler, Stopwatch $stopwatch = null)
    {
        $this->handler = $handler;
        $this->stopwatch = $stopwatch;
    }

    /**
     * @see eZ\Publish\SPI\Search\Content\Handler::findContent
     */
    function findContent(Query $query, array $languageFilter = array())
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $return = $this->handler->findContent($query, $languageFilter);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Search\Content\Handler::findSingle
     */
    public function findSingle(Criterion $filter, array $languageFilter = array())
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $return = $this->handler->findSingle($filter, $languageFilter);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Search\Content\Handler::findLocations
     */
    public function findLocations(LocationQuery $query, array $languageFilter = array())
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $return = $this->handler->findLocations($query, $languageFilter);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Search\Content\Handler::suggest
     */
    public function suggest($prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $return = $this->handler->suggest($prefix, $fieldPaths, $limit, $filter);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Search\Content\Handler::indexContent
     */
    public function indexContent(Content $content)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $this->handler->indexContent($content);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }
    }

    /**
     * @see eZ\Publish\SPI\Search\Content\Handler::deleteContent
     */
    public function deleteContent($contentID, $versionID = null)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $this->handler->deleteContent($contentID, $versionID);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }
    }

    public function indexLocation(Location $location)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $this->handler->indexLocation($location);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }
    }

    /**
     * @see \eZ\Publish\SPI\Search\Handler::deleteLocation
     */
    public function deleteLocation($locationId, $contentId)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $this->handler->deleteLocation($locationId, $contentId);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }
    }

    /**
     * @internal
     * @see \eZ\Publish\Core\Search\Solr\Handler::bulkIndexContent
     */
    public function bulkIndexContent(array $contentObjects)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $this->handler->bulkIndexContent($contentObjects);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }
    }

    /**
     * @internal
     * @see \eZ\Publish\Core\Search\Solr\Handler::purgeIndex
     */
    public function purgeIndex()
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

        $this->handler->purgeIndex();

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }
    }

    /**
     * @internal
     * @see \eZ\Publish\Core\Search\Solr\Handler::commit
     */
    public function commit($flush = false)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start(__METHOD__, 'ez.spi.search');
        }

       $this->handler->commit($flush);

        if ($this->stopwatch !== null) {
            $this->stopwatch->stop(__METHOD__);
        }
    }
}
