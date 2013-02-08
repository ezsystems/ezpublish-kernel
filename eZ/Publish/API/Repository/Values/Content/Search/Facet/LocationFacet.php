<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\Facet\FieldFacet class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\Facet;

use eZ\Publish\API\Repository\Values\Content\Search\Facet;

/**
 * Facet containing counts for content below child locations
 */
class LocationFacet extends Facet
{
    /**
     * An array with location id as key and count of matching content objects which are below this location as value
     *
     * @var array
     */
    public $entries;
}
