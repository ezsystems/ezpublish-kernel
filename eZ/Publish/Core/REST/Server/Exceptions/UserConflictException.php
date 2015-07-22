<?php

/**
 * File containing the UserConflictException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Exceptions;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserConflictException extends AuthenticationException
{
}
