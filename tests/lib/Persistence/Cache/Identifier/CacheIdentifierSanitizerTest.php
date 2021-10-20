<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Core\Persistence\Cache\Identifier;

use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CacheIdentifierSanitizerTest extends TestCase
{
    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierSanitizer */
    private $cacheIdentifierSanitizer;

    public function setUp(): void
    {
        $this->cacheIdentifierSanitizer = new CacheIdentifierSanitizer();
    }

    public function providerForTestEscapeCacheKey(): array
    {
        return [
            [['key'], 'key'],
            [['_key'], '__key'],
            [[':key(@cache{item})'], '_Ckey_BO_Acache_CBOitem_CBC_BC'],
        ];
    }

    /**
     * @dataProvider providerForTestEscapeCacheKey
     */
    public function testEscapeCacheKey(array $arguments, string $resultKey): void
    {
        $this->assertEquals(
            $resultKey,
            $this->cacheIdentifierSanitizer->escapeForCacheKey(...$arguments)
        );
    }
}
