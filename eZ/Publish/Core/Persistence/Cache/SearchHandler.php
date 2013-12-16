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

/**
 * @see eZ\Publish\SPI\Persistence\Content\Search\Handler
 */
class SearchHandler extends AbstractHandler implements SearchHandlerInterface
{
    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::findContent
     */
    function findContent( Query $query, array $fieldFilters = array() )
    {
        $this->logger->logCall( __METHOD__, array( 'query' => get_class( $query ), 'fieldFilters' => $fieldFilters ) );
        return $this->persistenceFactory->getSearchHandler()->findContent( $query, $fieldFilters );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::findSingle
     */
    public function findSingle( Criterion $filter, array $fieldFilters = array() )
    {
        $this->logger->logCall( __METHOD__, array( 'filter' => get_class( $filter ), 'fieldFilters' => $fieldFilters ) );
        return $this->persistenceFactory->getSearchHandler()->findSingle( $filter, $fieldFilters );
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
                'filter' => ( $filter === null ? 'null' : get_class( $filter ) )
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

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Search\Handler::deleteLocation
     */
    public function deleteLocation( $locationId )
    {
        $this->logger->logCall( __METHOD__, array( 'location' => $locationId ) );
        $this->persistenceFactory->getSearchHandler()->deleteLocation( $locationId );
    }

    /**
     * Indexes several content objects at once
     *
     * @todo: This function and setCommit() is needed for Persistence\Solr for test speed but not part
     *       of interface for the reason described in Solr\Content\Search\Gateway\Native::bulkIndexContent
     *       Short: Bulk handling should be properly designed before added to the interface.
     *
     * @param \eZ\Publish\SPI\Persistence\Content[] $contentObjects
     *
     * @return void
     */
    public function bulkIndexContent( array $contentObjects )
    {
        $this->persistenceFactory->getSearchHandler()->bulkIndexContent( $contentObjects );
    }

    /**
     * Set if index/delete actions should commit or if several actions is to be expected
     *
     * This should be set to false before group of actions and true before the last one
     * (also, see note on bulkIndexContent())
     * @param bool $commit
     */
    public function setCommit( $commit )
    {
       $this->persistenceFactory->getSearchHandler()->setCommit( $commit );
    }
}
