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

        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, array|string? $key, mixed? $return
        return [
            ['create', ['/home/about', '/web3/some/page/link', true], [['url_wildcard_not_found', [], false]], ['urlwnf'], null, $wildcard],
            ['remove', [1], [['url_wildcard', [1], false]], ['urlw-1']],
            ['loadAll', [], null, null, null, [$wildcard]],
            ['exactSourceUrlExists', ['/home/about'], null, null, null, true],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $wildcard = new UrlWildcard(['id' => 1]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            ['load', [1], 'ez-urlw-1', [['url_wildcard', [1], true]], ['ez-urlw-1'], $wildcard],
            ['translate', ['/home/about'], 'ez-urlws-_Shome_Sabout', [['url_wildcard_source', ['_Shome_Sabout'], true]], ['ez-urlws-_Shome_Sabout'], $wildcard],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $wildcard = new UrlWildcard(['id' => 1]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            [
                'load',
                [1],
                'ez-urlw-1',
                [
                    ['url_wildcard', [1], true],
                    ['url_wildcard', [1], false],
                ],
                ['ez-urlw-1', 'urlw-1'],
                $wildcard
            ],
            [
                'translate',
                ['/home/about'],
                'ez-urlws-_Shome_Sabout',
                [
                    ['url_wildcard_source', ['_Shome_Sabout'], true],
                    ['url_wildcard', [1], false],
                ],
                ['ez-urlws-_Shome_Sabout', 'urlw-1'],
                $wildcard
            ],
        ];
    }
}
