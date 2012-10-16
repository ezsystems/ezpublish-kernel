<?php
/**
 * File containing the LocationList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Location list view model
 */
class LocationList extends RestValue
{
    /**
     * Locations
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public $locations;

    /**
     * Path used to load this list of locations
     *
     * @var string
     */
    public $path;

    /**
     * Construct
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $locations
     * @param string $path
     */
    public function __construct( array $locations, $path )
    {
        $this->locations = $locations;
        $this->path = $path;
    }
}
