<?php
/**
 * File containing the Content Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content;

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
     * @return mixed
     */
    abstract public function find( Query $query, array $fieldFilters = array() );

    /**
     * Indexes a block of documents, which in our case is a Content preceded by its Locations.
     * In Solr block is identifiable by '_root_' field which holds a parent document (Content) id.
     *
     * @param \eZ\Publish\SPI\Search\Document[] $documents
     */
    abstract public function bulkIndexDocuments( array $documents );

    /**
     *
     * @param string $query
     */
    abstract public function deleteByQuery( $query );

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
