<?php

/**
 * File containing the eZ\Publish\API\Repository\Exceptions\BadStateException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Exceptions;

/**
 * This Exception is thrown if a method is called with an value referencing an object which is not in the right state.
 */
abstract class BadStateException extends ForbiddenException
{
}
