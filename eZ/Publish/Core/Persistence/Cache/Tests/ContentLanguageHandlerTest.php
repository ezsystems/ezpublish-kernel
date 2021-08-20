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

        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, array? $key
        return [
            [
                'create',
                [new SPILanguageCreateStruct()],
                [
                    ['language_list', [], true],
                ],
                null,
                ['ez-lal'],
            ],
            [
                'update',
                [$language],
                [
                    ['language_list', [], true],
                    ['language', [5], true],
                    ['language_code', ['eng-GB'], true],
                ],
                null,
                ['ez-lal', 'ez-la-5', 'ez-lac-eng-GB']
            ],
            [
                'delete',
                [5],
                [
                    ['language', [5], false]
                ],
                ['la-5'],
            ],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $object = new SPILanguage(['id' => 5, 'languageCode' => 'eng-GB']);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data, bool $multi
        return [
            ['load', [5], 'ez-la-5', [['language', [], true]], ['ez-la'], $object],
            ['loadList', [[5]], 'ez-la-5', [['language', [], true]], ['ez-la'], [5 => $object], true],
            ['loadAll', [], 'ez-lal', [['language_list', [], true]], ['ez-lal'], [5 => $object], false],
            ['loadByLanguageCode', ['eng-GB'], 'ez-lac-eng-GB', [['language_code', [], true]], ['ez-lac'], $object],
            ['loadListByLanguageCodes', [['eng-GB']], 'ez-lac-eng-GB', [['language_code', [], true]], ['ez-lac'], ['eng-GB' => $object], true],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $object = new SPILanguage(['id' => 5, 'languageCode' => 'eng-GB']);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data, bool $multi
        return [
            [
                'load',
                [5],
                'ez-la-5',
                [
                    ['language', [], true],
                    ['language', [5], false],
                ],
                ['ez-la', 'ez-la-5'],
                $object
            ],
            [
                'loadList',
                [[5]],
                'ez-la-5',
                [
                    ['language', [], true],
                    ['language', [5], false],
                ],
                ['ez-la', 'la-5'],
                [5 => $object],
                true
            ],
            [
                'loadAll',
                [],
                'ez-lal',
                [
                    ['language_list', [], true],
                    ['language', [5], false],
                ],
                ['ez-lal', 'la-5'],
                [5 => $object],
                false
            ],
            [
                'loadByLanguageCode',
                ['eng-GB'],
                'ez-lac-eng-GB',
                [
                    ['language_code', [], true],
                    ['language', [5], false],
                ],
                ['ez-lac', 'la-5'],
                $object
            ],
            [
                'loadListByLanguageCodes',
                [['eng-GB']],
                'ez-lac-eng-GB',
                [
                    ['language_code', [], true],
                    ['language', [5], false],
                ],
                ['ez-lac', 'la-5'],
                ['eng-GB' => $object],
                true
            ],
        ];
    }
}
