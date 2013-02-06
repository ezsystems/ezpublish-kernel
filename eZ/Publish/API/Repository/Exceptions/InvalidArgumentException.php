<?php
/**
 * File containing the eZ\Publish\API\Repository\Exceptions\InvalidArgumentException class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Exceptions;

/**
 *
 * This exception is thrown if a service method is called with an illegal or non appropriate value
 */
abstract class InvalidArgumentException extends ForbiddenException
{
}
