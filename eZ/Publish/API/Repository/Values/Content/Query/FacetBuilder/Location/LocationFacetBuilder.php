<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\Location\LocationFacetBuilder class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\Location;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\Location;

/**
 * Build a Subtree facet
 *
 * If provided the search service returns a LocationFacet which
 * contains the counts for all Locations below the child Locations.
 *
 * @todo: check hierarchical facets
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */
class LocationFacetBuilder extends Location
{
    /**
     * The parent location
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location;
}
