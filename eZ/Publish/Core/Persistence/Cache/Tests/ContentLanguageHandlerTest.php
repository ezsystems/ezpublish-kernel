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
            ['create', [new SPILanguageCreateStruct()], null, ['ez-language-list']],
            ['update', [$language], null, ['ez-language-list', 'ez-language-5', 'ez-language-code-eng-GB']],
            ['delete', [5], ['language-5']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $object = new SPILanguage(['id' => 5, 'languageCode' => 'eng-GB']);

        // string $method, array $arguments, string $key, mixed? $data, bool $multi
        return [
            ['load', [5], 'ez-language-5', $object],
            ['loadList', [[5]], 'ez-language-5', [5 => $object], true],
            ['loadAll', [], 'ez-language-list', [5 => $object], false],
            ['loadByLanguageCode', ['eng-GB'], 'ez-language-code-eng-GB', $object],
            ['loadListByLanguageCodes', [['eng-GB']], 'ez-language-code-eng-GB', ['eng-GB' => $object], true],
        ];
    }
}
