<?php

/**
 * Contains Database Exception implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Exceptions;

use RuntimeException;

/**
 * An exception that is thrown when the database encounters an error.
 */
final class DatabaseException extends RuntimeException
{
    public const MESSAGE = 'Database error';
}
