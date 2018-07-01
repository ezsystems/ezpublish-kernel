<?php

/**
 * File containing the eZ\Publish\API\Repository\Exceptions\ForbiddenException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Exceptions;

use Exception;

/**
 * An Exception which is thrown if an operation cannot be performed by a service
 * although the current user would have permission to perform this action.
 */
abstract class ForbiddenException extends Exception
{
}
