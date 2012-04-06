<?php
/**
 * File containing the InvalidObjectCount class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Exception;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Exception thrown when a result had an invalid object count
 */
class InvalidObjectCount extends NotFoundException
{
}
