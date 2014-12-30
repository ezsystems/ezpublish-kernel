<?php
/**
 * File containing the Location Search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Location;

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
}
