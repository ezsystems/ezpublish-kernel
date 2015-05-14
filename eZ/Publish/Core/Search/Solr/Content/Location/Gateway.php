<?php
/**
 * File containing the Location Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Location;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;

/**
 * The Location Search Gateway provides the implementation for one database to
 * retrieve the desired Location objects.
 */
abstract class Gateway
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    abstract public function findLocations( LocationQuery $query );

    /**
     * Indexes a block of documents, which in our case is a Content preceded by its Locations.
     * In Solr block is identifiable by '_root_' field which holds a parent document (Content) id.
     *
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     */
    abstract public function bulkIndexDocuments( array $documents );

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
