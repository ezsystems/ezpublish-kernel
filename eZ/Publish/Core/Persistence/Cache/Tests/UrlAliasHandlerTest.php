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
        // string $method, array $arguments, array? $tags, array? $key, mixed? $returnValue
        return [
            ['publishUrlAliasForLocation', [44, 2, 'name', 'eng-GB', true, false], ['urlal-44', 'urlalp-44', 'urlanf']],
            ['createCustomUrlAlias', [44, '1/2/44', true, null, false], ['urlal-44', 'urlalp-44', 'urlanf', 'urla-5'], null, new UrlAlias(['id' => 5])],
            ['createGlobalUrlAlias', ['something', '1/2/44', true, null, false], ['urlanf']],
            ['createGlobalUrlAlias', ['something', '1/2/44', true, 'eng-GB', false], ['urlanf']],
            ['listGlobalURLAliases', ['eng-GB', 10, 50]],
            ['removeURLAliases', [[new UrlAlias(['id' => 5, 'type' => UrlAlias::LOCATION, 'isCustom' => true, 'destination' => 21])]], ['urla-5', 'urlal-21', 'urlalp-21', 'urlac-21']],
            ['locationMoved', [21, 45, 12], ['urlal-21', 'urlalp-21']],
            ['locationCopied', [21, 33, 12], ['urlal-21', 'urlal-33']],
            ['locationDeleted', [21], ['urlal-21', 'urlalp-21'], null, []],
            ['locationSwapped', [21, 2, 33, 45], ['urlal-21', 'urlalp-21', 'urlal-33', 'urlalp-33']],
            ['translationRemoved', [[21, 33], 'eng-GB'], ['urlal-21', 'urlalp-21', 'urlal-33', 'urlalp-33']],
            ['archiveUrlAliasesForDeletedTranslations', [21, 33, ['eng-GB']], ['urlal-21', 'urlalp-21']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $object = new UrlAlias(['id' => 5]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['listURLAliasesForLocation', [5], 'ez-urlall-5', [$object]],
            ['listURLAliasesForLocation', [5, true], 'ez-urlall-5-custom', [$object]],
            ['lookup', ['/Home'], 'ez-urlau-_SHome', $object],
            ['loadUrlAlias', [5], 'ez-urla-5', $object],
        ];
    }
}
