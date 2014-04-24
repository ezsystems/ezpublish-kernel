<?php
/**
 * File containing the InvalidUserTypeException class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Exceptions;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidUserTypeException extends AuthenticationException
{
}
