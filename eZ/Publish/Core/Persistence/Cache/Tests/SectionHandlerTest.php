<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Section as SPISection;
use eZ\Publish\SPI\Persistence\Content\Section\Handler;

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
        return Handler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['create', ['Standard', 'standard']],
            ['update', [5, 'Standard', 'standard'], ['section-5']],
            ['loadAll', []],
            ['delete', [5], ['section-5']],
            ['assign', [5, 42], ['content-42']],
            ['assignmentsCount', [5]],
            ['policiesCount', [5]],
            ['countRoleAssignmentsUsingSection', [5]],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $object = new SPISection(['id' => 5]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['load', [5], 'ez-section-5', $object],
            ['loadByIdentifier', ['standard'], 'ez-section-standard-by-identifier', $object],
        ];
    }
}
