<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Search\Handler as SearchHandlerInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Search\Handler
 */
class SearchHandler extends SearchHandlerInterface
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * @var PersistenceLogger
     */
    protected $logger;

    /**
     * Setups current handler with everything needed
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cache
     * @param \eZ\Publish\Core\Persistence\Factory $persistenceFactory
     * @param PersistenceLogger $logger
     */
    public function __construct(
        CacheService $cache,
        PersistenceFactory $persistenceFactory,
        PersistenceLogger $logger )
    {
        $this->cache = $cache;
        $this->persistenceFactory = $persistenceFactory;
        $this->logger = $logger;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::findContent
     */
    function findContent( Query $query, array $fieldFilters = array() )
    {
        $this->logger->logCall( __METHOD__, array( 'query' => $query, 'fieldFilters' => $fieldFilters ) );
        return $this->persistenceFactory->getSearchHandler()->findContent( $query, $fieldFilters );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::findSingle
     */
    public function findSingle( Criterion $criterion, array $fieldFilters = array() )
    {
        $this->logger->logCall( __METHOD__, array( 'criterion' => $criterion, 'fieldFilters' => $fieldFilters ) );
        return $this->persistenceFactory->getSearchHandler()->findSingle( $criterion, $fieldFilters );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::suggest
     */
    public function suggest( $prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null )
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'prefix' => $prefix,
                'fieldPaths' => $fieldPaths,
                'limit' => $limit,
                'filter' => $filter
            )
        );

        return $this->persistenceFactory->getSearchHandler()->suggest( $prefix, $fieldPaths, $limit, $filter );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::indexContent
     */
    public function indexContent( Content $content )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $content->versionInfo->contentInfo->id ) );
        $this->persistenceFactory->getSearchHandler()->indexContent( $content );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::deleteContent
     */
    public function deleteContent( $contentID, $versionID = null )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentID, 'version' => $versionID ) );
        $this->persistenceFactory->getSearchHandler()->deleteContent( $contentID, $versionID );
    }
}
