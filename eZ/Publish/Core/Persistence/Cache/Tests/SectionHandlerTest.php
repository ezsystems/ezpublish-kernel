<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Section as SPISection;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SPISectionHandler;

/**
 * Test case for Persistence\Cache\SectionHandler.
 */
class SectionHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'sectionHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPISectionHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $cacheTagGeneratingArguments, array? $cacheKeyGeneratingArguments, array? $tags, string? $key
        return [
            ['create', ['Standard', 'standard']],
            ['update', [5, 'Standard', 'standard'], [['section', [5], false]], null, ['se-5']],
            ['loadAll', []],
            ['delete', [5], [['section', [5], false]], null, ['se-5']],
            ['assign', [5, 42], [['content', [42], false]], null, ['c-42']],
            ['assignmentsCount', [5]],
            ['policiesCount', [5]],
            ['countRoleAssignmentsUsingSection', [5]],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $object = new SPISection(['id' => 5]);

        // string $method, array $arguments string $key, array? $cacheIdentifierGeneratorArguments, array? $cacheIdentifierGeneratorResults, mixed? $data
        return [
            ['load', [5], 'ibx-se-5', [['section', [5], true]], ['ibx-se-5'], $object],
            ['loadByIdentifier', ['standard'], 'ibx-se-standard-bi', [['section_with_by_id', ['standard'], true]], ['ibx-se-standard-bi'], $object],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $object = new SPISection(['id' => 5]);

        // string $method, array $arguments string $key, array? $cacheIdentifierGeneratorArguments, array? $cacheIdentifierGeneratorResults, mixed? $data
        return [
            [
                'load',
                [5],
                'ibx-se-5',
                [
                    ['section', [5], true],
                    ['section', [5], false],
                ],
                ['ibx-se-5', 'se-5'],
                $object,
            ],
            [
                'loadByIdentifier',
                ['standard'],
                'ibx-se-standard-bi',
                [
                    ['section_with_by_id', ['standard'], true],
                    ['section', [5], false],
                ],
                ['ibx-se-standard-bi', 'se-5'],
                $object,
            ],
        ];
    }
}
