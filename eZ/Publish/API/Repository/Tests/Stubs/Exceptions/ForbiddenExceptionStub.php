<?php
/**
 * File containing the eZ\Publish\API\Repository\Exceptions\ForbiddenException class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Exceptions;

use eZ\Publish\API\Repository\Exceptions;

/**
 * An Exception which is thrown if an operation cannot be performed by a service
 * although the current user would have permission to perform this action.
 */
class ForbiddenExceptionStub extends Exceptions\ForbiddenException
{
}
