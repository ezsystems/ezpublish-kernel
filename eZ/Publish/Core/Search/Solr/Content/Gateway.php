<?php

/**
 * File containing the Content Search Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
    abstract public function find(Query $query, array $fieldFilters = array());

    /**
     * Indexes an array of documents.
     *
     * Documents are given as an array of the array of documents. The array of documents
     * holds documents for all translations of the particular entity.
     *
     * @param \eZ\Publish\SPI\Search\Document[][] $documents
     */
    abstract public function bulkIndexDocuments(array $documents);

    /**
     * Deletes documents by the given $query.
     *
     * @param string $query
     */
    abstract public function deleteByQuery($query);

    /**
     * Purges all contents from the index.
     */
    abstract public function purgeIndex();

    /**
     * Set if index/delete actions should commit or if several actions is to be expected.
     *
     * This should be set to false before group of actions and true before the last one
     *
     * @param bool $commit
     */
    abstract public function setCommit($commit);
}
