<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Tests\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\User\PasswordBlacklist\Handler as SPIPasswordBlacklistHandler;
use eZ\Publish\Core\Persistence\Cache\Tests\AbstractCacheHandlerTest;

class PasswordBlacklistHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'passwordBlacklistHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPIPasswordBlacklistHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key, mixed $returnValue
        return [
            ['isBlacklisted', ['pass'], [], null, false],
            ['removeAll', [], [], null, null],
            ['insert', [['123456', 'qwerty', 'password']], [], null, null],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        return [];
    }
}
