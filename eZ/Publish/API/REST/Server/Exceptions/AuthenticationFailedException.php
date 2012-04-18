<?php
/**
 * File containing the Authenticator used for integration tests
 *
 * ATTENTION: This is a only meant for the test setup for the REST server. DO
 * NOT USE IT IN PRODUCTION!
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Exceptions;

/**
 * Exception thrown if authentication credentials were provided by the
 * authentication failed.
 */
class AuthenticationFailedException extends \InvalidArgumentException
{
}
