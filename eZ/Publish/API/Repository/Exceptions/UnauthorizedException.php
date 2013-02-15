<?php
/**
 * File containing the eZ\Publish\API\Repository\Exceptions\UnauthorizedException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Exceptions;

use Exception;

/**
 * This Exception is thrown if the user has is not allowed to perform a service operation
 */
abstract class UnauthorizedException extends Exception
{
}

