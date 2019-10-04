<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Base\Exceptions;

use RuntimeException;
use Throwable;

/**
 * An exception that is thrown when the database encounters an error.
 */
final class DatabaseException extends RuntimeException
{
    public const DEFAULT_MESSAGE = 'Database error';

    public static function wrap(
        Throwable $previous,
        string $message = self::DEFAULT_MESSAGE,
        int $code = 0
    ): self {
        return new self($message, $code, $previous);
    }
}
