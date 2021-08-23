<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\UrlAlias;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as SPIUrlAliasHandler;

/**
 * Test case for Persistence\Cache\UrlAliasHandler.
 */
class UrlAliasHandlerTest extends AbstractInMemoryCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'urlAliasHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPIUrlAliasHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, array? $key, mixed? $returnValue
        return [
            [
                'publishUrlAliasForLocation',
                [44, 2, 'name', 'eng-GB', true, false],
                [
                    ['url_alias_location', [44], false],
                    ['url_alias_location_path', [44], false],
                    ['url_alias_not_found', [], false],
                ],
                ['urlal-44', 'urlalp-44', 'urlanf'],
            ],
            [
                'createCustomUrlAlias',
                [44, '1/2/44', true, null, false],
                [
                    ['url_alias_location', [44], false],
                    ['url_alias_location_path', [44], false],
                    ['url_alias_not_found', [], false],
                    ['url_alias', [5], false],
                ],
                ['urlal-44', 'urlalp-44', 'urlanf', 'urla-5'],
                null,
                new UrlAlias(['id' => 5]),
            ],
            ['createGlobalUrlAlias', ['something', '1/2/44', true, null, false], [['url_alias_not_found', [], false]], ['urlanf']],
            ['createGlobalUrlAlias', ['something', '1/2/44', true, 'eng-GB', false], [['url_alias_not_found', [], false]], ['urlanf']],
            ['listGlobalURLAliases', ['eng-GB', 10, 50]],
            [
                'removeURLAliases',
                [[new UrlAlias(['id' => 5, 'type' => UrlAlias::LOCATION, 'isCustom' => true, 'destination' => 21])]],
                [
                    ['url_alias', [5], false],
                    ['url_alias_location', [21], false],
                    ['url_alias_location_path', [21], false],
                    ['url_alias_custom', [21], false],
                ],
                ['urla-5', 'urlal-21', 'urlalp-21', 'urlac-21'],
            ],
            [
                'locationMoved',
                [21, 45, 12],
                [
                    ['url_alias_location', [21], false],
                    ['url_alias_location_path', [21], false],
                ],
                ['urlal-21', 'urlalp-21'],
            ],
            [
                'locationCopied',
                [21, 33, 12],
                [
                    ['url_alias_location', [21], false],
                    ['url_alias_location', [33], false],
                ],
                ['urlal-21', 'urlal-33'],
            ],
            [
                'locationDeleted',
                [21],
                [
                    ['url_alias_location', [21], false],
                    ['url_alias_location_path', [21], false],
                ],
                ['urlal-21', 'urlalp-21'],
                null,
                [],
            ],
            [
                'locationSwapped',
                [21, 2, 33, 45],
                [
                    ['url_alias_location', [21], false],
                    ['url_alias_location_path', [21], false],
                    ['url_alias_location', [33], false],
                    ['url_alias_location_path', [33], false],
                ],
                ['urlal-21', 'urlalp-21', 'urlal-33', 'urlalp-33'],
            ],
            [
                'translationRemoved',
                [[21, 33], 'eng-GB'],
                [
                    ['url_alias_location', [21], false],
                    ['url_alias_location_path', [21], false],
                    ['url_alias_location', [33], false],
                    ['url_alias_location_path', [33], false],
                ],
                ['urlal-21', 'urlalp-21', 'urlal-33', 'urlalp-33'],
            ],
            [
                'archiveUrlAliasesForDeletedTranslations',
                [21, 33, ['eng-GB']],
                [
                    ['url_alias_location', [21], false],
                    ['url_alias_location_path', [21], false],
                ],
                ['urlal-21', 'urlalp-21'],
            ],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $object = new UrlAlias(['id' => 5]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            ['listURLAliasesForLocation', [5], 'ez-urlall-5', [['url_alias_location_list', [5], true]], ['ez-urlall-5'], [$object]],
            ['listURLAliasesForLocation', [5, true], 'ez-urlall-5-c', [['url_alias_location_list_custom', [5], true]], ['ez-urlall-5-c'], [$object]],
            ['lookup', ['/Home'], 'ez-urlau-_SHome', [['url_alias_url', ['_SHome'], true]], ['ez-urlau-_SHome'], $object],
            ['loadUrlAlias', [5], 'ez-urla-5', [['url_alias', [5], true]], ['ez-urla-5'], $object],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $object = new UrlAlias(['id' => 5]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            [
                'listURLAliasesForLocation',
                [5],
                'ez-urlall-5',
                [
                    ['url_alias_location_list', [5], true],
                    ['url_alias_location', [5], false],
                    ['url_alias', [5], false],
                ],
                ['ez-urlall-5', 'urlal-5', 'urla-5'],
                [$object],
            ],
            [
                'listURLAliasesForLocation',
                [5, true],
                'ez-urlall-5-c',
                [
                    ['url_alias_location_list_custom', [5], true],
                    ['url_alias_location', [5], false],
                    ['url_alias', [5], false],
                ],
                ['ez-urlall-5-c', 'urlal-5', 'urla-5'],
                [$object],
            ],
            [
                'lookup',
                ['/Home'],
                'ez-urlau-_SHome',
                [
                    ['url_alias_url', ['_SHome'], true],
                    ['url_alias', [5], false],
                ],
                ['ez-urlau-_SHome', 'urla-5'],
                $object,
            ],
            [
                'loadUrlAlias',
                [5],
                'ez-urla-5',
                [
                    ['url_alias', [5], true],
                    ['url_alias', [5], false],
                ],
                ['ez-urla-5', 'urla-5'],
                $object,
            ],
        ];
    }
}
