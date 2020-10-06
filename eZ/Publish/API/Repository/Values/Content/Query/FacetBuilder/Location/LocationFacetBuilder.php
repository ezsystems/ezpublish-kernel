<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\Location;

use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\Location;

/**
 * Build a Subtree facet.
 *
 * If provided the search service returns a LocationFacet which
 * contains the counts for all Locations below the child Locations.
 *
 * @todo: check hierarchical facets
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
 */
class LocationFacetBuilder extends Location
{
    /**
     * The parent location.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location;
}
