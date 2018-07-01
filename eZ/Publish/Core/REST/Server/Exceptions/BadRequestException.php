<?php

/**
 * File containing the BadRequestException tests.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown if the request is not formatted correctly.
 */
class BadRequestException extends InvalidArgumentException
{
}
