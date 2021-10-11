<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Core\Persistence\Cache;

use Ibexa\Core\Persistence\Cache\CacheIdentifierTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CacheIdentifierTraitTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $mock;

    public function setUp(): void
    {
        $this->mock = $this
            ->getMockBuilder(CacheIdentifierTrait::class)
            ->getMockForTrait();
    }

    public function providerForTestEscapeCacheKey(): array
    {
        return [
            [['key'], 'key'],
            [['_key'], '__key'],
            [[':key(@cache{item})'], '_Ckey_BO_Acache_CBOitem_CBC_BC'],
        ];
    }

    public function providerForTestRemoveRootLocationPathId(): array
    {
        return [
            [[[]], []],
            [[[1, 2, 3]], [2, 3]],
            [[[34, 45]], [34, 45]],
            [[[1]], []],
        ];
    }

    /**
     * @dataProvider providerForTestEscapeCacheKey
     */
    public function testEscapeCacheKey(array $arguments, string $resultKey): void
    {
        $this->assertEquals($resultKey, $this->mock->escapeForCacheKey(...$arguments));
    }

    /**
     * @dataProvider providerForTestRemoveRootLocationPathId
     */
    public function testRemoveRootLocationPathId(array $arguments, array $resultArray): void
    {
        $this->assertEquals($resultArray, $this->mock->removeRootLocationPathId(...$arguments));
    }
}
