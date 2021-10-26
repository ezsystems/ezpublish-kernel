<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Core\Persistence\Cache\Identifier;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CacheIdentifierGeneratorTest extends TestCase
{
    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface */
    private $cacheIdentifierGenerator;

    public function setUp(): void
    {
        $this->cacheIdentifierGenerator = new CacheIdentifierGenerator('ibx-',
            [
                'content' => 'c-%s',
                'content_version' => 'c-%s-v-%s',
                'content_locations_with_parent_for_draft_suffix' => 'cl-%s-pfd',
            ],
            [
                'content_type' => 'ct-%s',
                'by_remote_suffix' => '-br',
                'image_variation_field' => 'igf-%s',
            ]
        );
    }

    public function providerForTestGenerateTag(): array
    {
        return [
            [['content', [], false], 'c'],
            [['content_version', [1, 2], true], 'ibx-c-1-v-2'],
            [['content_locations_with_parent_for_draft_suffix', [3], true], 'ibx-cl-3-pfd'],
        ];
    }

    public function providerForTestGenerateTagThrowsInvalidArgumentException(): array
    {
        return [
            [['test', [], false], 'c'],
            [['some_invalid_pattern', [1, 2], true], 'ibx-c-1-v-2'],
        ];
    }

    public function providerForTestGenerateKey(): array
    {
        return [
            [['content_type', [], true], 'ibx-ct'],
            [['by_remote_suffix', [], false], '-br'],
            [['image_variation_field', [3], true], 'ibx-igf-3'],
        ];
    }

    public function providerForTestGenerateKeyThrowsInvalidArgumentException(): array
    {
        return [
            [['test', [], false], 'c'],
            [['some_invalid_pattern', [1, 2], true], 'ibx-c-1-v-2'],
        ];
    }

    /**
     * @dataProvider providerForTestGenerateTag
     */
    public function testGenerateTag(array $arguments, string $resultKey): void
    {
        $this->assertEquals(
            $resultKey,
            $this->cacheIdentifierGenerator->generateTag(...$arguments)
        );
    }

    /**
     * @dataProvider providerForTestGenerateTagThrowsInvalidArgumentException
     */
    public function testGenerateTagThrowsInvalidArgumentException(array $arguments, string $resultKey): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->assertEquals(
            $resultKey,
            $this->cacheIdentifierGenerator->generateTag(...$arguments)
        );
    }

    /**
     * @dataProvider providerForTestGenerateKey
     */
    public function testGenerateKey(array $arguments, string $resultKey): void
    {
        $this->assertEquals(
            $resultKey,
            $this->cacheIdentifierGenerator->generateKey(...$arguments)
        );
    }

    /**
     * @dataProvider providerForTestGenerateKeyThrowsInvalidArgumentException
     */
    public function testGenerateKeyThrowsInvalidArgumentException(array $arguments, string $resultKey): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->assertEquals(
            $resultKey,
            $this->cacheIdentifierGenerator->generateKey(...$arguments)
        );
    }
}
