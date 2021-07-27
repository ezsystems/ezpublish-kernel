<?php

/**
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
class ContentLanguageHandlerTest extends AbstractInMemoryCacheHandlerTest
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
        $language = new SPILanguage(['id' => 5, 'languageCode' => 'eng-GB']);

        // string $method, array $arguments, array? $tags, array? $key
        return [
            ['create', [new SPILanguageCreateStruct()], null, ['ez-lal']],
            ['update', [$language], null, ['ez-lal', 'ez-la-5', 'ez-lac-eng-GB']],
            ['delete', [5], ['la-5']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $object = new SPILanguage(['id' => 5, 'languageCode' => 'eng-GB']);

        // string $method, array $arguments, string $key, mixed? $data, bool $multi
        return [
            ['load', [5], 'ez-la-5', $object],
            ['loadList', [[5]], 'ez-la-5', [5 => $object], true],
            ['loadAll', [], 'ez-lal', [5 => $object], false],
            ['loadByLanguageCode', ['eng-GB'], 'ez-lac-eng-GB', $object],
            ['loadListByLanguageCodes', [['eng-GB']], 'ez-lac-eng-GB', ['eng-GB' => $object], true],
        ];
    }
}
