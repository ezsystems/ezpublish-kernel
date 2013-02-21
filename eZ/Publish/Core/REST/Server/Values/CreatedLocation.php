<?php
/**
 * File containing the CreatedLocation class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created location.
 */
class CreatedLocation extends ValueObject
{
    /**
     * The created location
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestLocation
     */
    public $restLocation;
}
