<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler as SpiUrlWildcardHandler;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

class UrlWildcardHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'urlWildcardHandler';
    }

    public function getHandlerClassName(): string
    {
        return SpiUrlWildcardHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        $wildcard = new UrlWildcard(['id' => 1]);

        // string $method, array $arguments, array? $tags, array|string? $key, mixed? $return
        return [
            ['create', ['/home/about', '/web3/some/page/link', true], ['urlWildcard-notFound'], null, $wildcard],
            ['update', [1, '/home/about-updated', '/web3/some/page/link-updated', true], ['urlWildcard-notFound', 'urlWildcard-1'], null, $wildcard],
            ['remove', [1], ['urlWildcard-1']],
            ['loadAll', [], null, null, [$wildcard]],
            ['exactSourceUrlExists', ['/home/about'], null, null, true],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $wildcard = new UrlWildcard(['id' => 1]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['load', [1], 'ez-urlWildcard-1', $wildcard],
            ['translate', ['/home/about'], 'ez-urlWildcard-source-_Shome_Sabout', $wildcard],
        ];
    }
}
