<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Language as SPILanguage;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct as SPILanguageCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Language\Handler;

/**
 * Test case for Persistence\Cache\ContentLanguageHandler.
 */
class ContentLanguageHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'contentLanguageHandler';
    }

    public function getHandlerClassName(): string
    {
        return Handler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['create', [new SPILanguageCreateStruct()]],
            ['update', [new SPILanguage(['id' => 5])], ['language-5']],
            ['loadAll', []],
            ['delete', [5], ['language-5']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $object = new SPILanguage(['id' => 5]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['load', [5], 'ez-language-5', $object],
            ['loadByLanguageCode', ['eng-GB'], 'ez-language-eng-GB-by-code', $object],
        ];
    }
}
