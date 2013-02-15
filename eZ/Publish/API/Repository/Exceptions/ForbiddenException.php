<?php
/**
 * File containing the eZ\Publish\API\Repository\Exceptions\ForbiddenException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Exceptions;

use Exception;

/**
 * An Exception which is thrown if an operation cannot be performed by a service
 * although the current user would have permission to perform this action.
 *
 * @package eZ\Publish\API\Repository\Exceptions
 */
abstract class ForbiddenException extends Exception
{
}
