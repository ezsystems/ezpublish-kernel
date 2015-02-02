<?php
/**
 * File containing the Elasticsearch Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * The Gateway provides the implementation to retrieve the desired
 * documents from Elasticsearch index storage.
 */
abstract class Gateway
{
    /**
     * Indexes a given $document.
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document $document
     */
    abstract public function index( Document $document );

    /**
     * Performs bulk index of a given array of documents.
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Document[] $documents
     */
    abstract public function bulkIndex( array $documents );

    /**
     * Finds and returns documents of a given $type for a given $query object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param string $type
     * @param array $fieldFilters
     *
     * @return mixed
     */
    abstract public function find( Query $query, $type, array $fieldFilters = array() );

    /**
     * Finds and returns documents of a given $type for a given $query string.
     *
     * @param string $query
     * @param string $type
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway\Message
     */
    abstract public function findRaw( $query, $type );

    /**
     * Deletes all documents of a given $type from the index.
     *
     * @param string $type
     */
    abstract public function purgeIndex( $type );

    /**
     * Deletes a single document of the given $type by given document $id.
     *
     * @param int|string $id
     * @param string $type
     */
    abstract public function delete( $id, $type );

    /**
     * Deletes a document(s) of the given $type by given $query string.
     *
     * @param string $query
     * @param string $type
     */
    abstract public function deleteByQuery( $query, $type );

    /**
     * Flushes data from memory to the index storage.
     */
    abstract public function flush();
}
