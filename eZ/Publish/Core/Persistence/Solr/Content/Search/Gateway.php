<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
abstract class Gateway
{
    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    abstract public function findContent( Query $query, array $fieldFilters = array() );

    /**
     * Indexes a content object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field[][] $documents
     *
     * @return void
     */
    abstract public function bulkIndexContent( array $documents );

    /**
     * Deletes a content object from the index
     *
     * @param int content id
     * @param int|null version id
     *
     * @return void
     */
    abstract public function deleteContent( $contentId, $versionId = null );

    /**
     * Deletes a location from the index
     *
     * @param mixed $locationId
     *
     * @return void
     */
    abstract public function deleteLocation( $locationId );

    /**
     * Purges all contents from the index
     *
     * @return void
     */
    abstract public function purgeIndex();

    /**
     * Set if index/delete actions should commit or if several actions is to be expected
     *
     * This should be set to false before group of actions and true before the last one
     *
     * @param bool $commit
     */
    abstract public function setCommit( $commit );
}

