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
 * The Elasticsearch Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
abstract class Gateway
{
    abstract public function index( Document $document );

    abstract public function bulkIndex( array $documents );

    abstract public function find( Query $query, $type );

    abstract public function findRaw( $query, $type );

    abstract public function purgeIndex( $type );

    abstract public function delete( $id, $type );

    abstract public function deleteByQuery( $query, $type );

    abstract public function flush();
}
