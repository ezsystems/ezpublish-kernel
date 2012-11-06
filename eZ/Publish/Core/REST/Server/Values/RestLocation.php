<?php
/**
 * File containing the RestLocation class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestLocation view model
 */
class RestLocation extends RestValue
{
    /**
     * A location
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    public $location;

    /**
     * Number of children of the location
     *
     * @var int
     */
    public $childCount;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param int $childCount
     */
    public function __construct( Location $location, $childCount )
    {
        $this->location = $location;
        $this->childCount = $childCount;
    }
}
