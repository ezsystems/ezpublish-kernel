<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\TaggedServiceIdsIterator;

/**
 * Captures user deprecation warnings emitted using trigger_error function.
 *
 * @internal
 */
final class DeprecationErrorCollector
{
    /** @var array */
    private $errors = [];

    /** @var callable|null */
    private $previousErrorHandler;

    public function register(): void
    {
        $this->previousErrorHandler = set_error_handler($this, E_USER_DEPRECATED);
    }

    public function unregister(): void
    {
        set_error_handler($this->previousErrorHandler);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function __invoke(int $code, string $message, string $file, int $line): bool
    {
        $this->errors[] = [
            'code' => $code,
            'message' => $message,
            'file' => $file,
            'line' => $line,
        ];

        return true;
    }
}
