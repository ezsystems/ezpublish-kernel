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

        // string $method, array $arguments, array? $tagGeneratingArguments, array? $keyGeneratingArguments, array? $tags, array? $key
        return [
            [
                'create',
                [new SPILanguageCreateStruct()],
                null,
                [
                    ['language_list', [], true],
                ],
                null,
                ['ibx-lal'],
            ],
            [
                'update',
                [$language],
                null,
                [
                    ['language_list', [], true],
                    ['language', [5], true],
                    ['language_code', ['eng-GB'], true],
                ],
                null,
                ['ibx-lal', 'ibx-la-5', 'ibx-lac-eng-GB'],
            ],
            [
                'delete',
                [5],
                [
                    ['language', [5], false],
                ],
                null,
                ['la-5'],
            ],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $object = new SPILanguage(['id' => 5, 'languageCode' => 'eng-GB']);

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $keyGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            ['load', [5], 'ibx-la-5', null, null, [['language', [], true]], ['ibx-la'], $object],
            ['loadList', [[5]], 'ibx-la-5', null, null, [['language', [], true]], ['ibx-la'], [5 => $object], true],
            ['loadAll', [], 'ibx-lal', null, null, [['language_list', [], true]], ['ibx-lal'], [5 => $object], false],
            ['loadByLanguageCode', ['eng-GB'], 'ibx-lac-eng-GB', null, null, [['language_code', [], true]], ['ibx-lac'], $object],
            ['loadListByLanguageCodes', [['eng-GB']], 'ibx-lac-eng-GB', null, null, [['language_code', [], true]], ['ibx-lac'], ['eng-GB' => $object], true],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $object = new SPILanguage(['id' => 5, 'languageCode' => 'eng-GB']);

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingArguments, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            [
                'load',
                [5],
                'ibx-la-5',
                [
                    ['language', [5], false],
                ],
                ['la-5'],
                [
                    ['language', [], true],
                ],
                ['ibx-la'],
                $object,
            ],
            [
                'loadList',
                [[5]],
                'ibx-la-5',
                [
                    ['language', [5], false],
                ],
                ['la-5'],
                [
                    ['language', [], true],
                ],
                ['ibx-la'],
                [5 => $object],
                true,
            ],
            [
                'loadAll',
                [],
                'ibx-lal',
                [
                    ['language', [5], false],
                ],
                ['la-5'],
                [
                    ['language_list', [], true],
                ],
                ['ibx-lal'],
                [5 => $object],
                false,
            ],
            [
                'loadByLanguageCode',
                ['eng-GB'],
                'ibx-lac-eng-GB',
                [
                    ['language', [5], false],
                ],
                ['la-5'],
                [
                    ['language_code', [], true],
                ],
                ['ibx-lac'],
                $object,
            ],
            [
                'loadListByLanguageCodes',
                [['eng-GB']],
                'ibx-lac-eng-GB',
                [
                    ['language', [5], false],
                ],
                ['la-5'],
                [
                    ['language_code', [], true],
                ],
                ['ibx-lac'],
                ['eng-GB' => $object],
                true,
            ],
        ];
    }
}
