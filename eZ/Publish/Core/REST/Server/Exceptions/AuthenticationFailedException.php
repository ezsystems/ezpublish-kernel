<?php
/**
 * File containing the AuthenticationFailedException tests
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Exceptions;

/**
 * Exception thrown if authentication credentials were provided by the
 * authentication failed.
 */
class AuthenticationFailedException extends \InvalidArgumentException
{
}
