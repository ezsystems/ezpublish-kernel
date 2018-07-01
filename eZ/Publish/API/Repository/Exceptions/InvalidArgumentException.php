<?php

/**
 * File containing the eZ\Publish\API\Repository\Exceptions\InvalidArgumentException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This exception is thrown if a service method is called with an illegal or non appropriate value.
 */
abstract class InvalidArgumentException extends ForbiddenException
{
}
