<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Core\Persistence\Cache\Tag;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Persistence\Cache\Tag\TagGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TagGeneratorTest extends TestCase
{
    /** @var \Ibexa\Core\Persistence\Cache\Tag\TagGeneratorInterface */
    private $tagGenerator;

    public function setUp(): void
    {
        $this->tagGenerator = new TagGenerator('ibx-',
            [
                'content' => 'c-%s',
                'content_version' => 'c-%s-v-%s',
                'content_locations_with_parent_for_draft_suffix' => 'cl-%s-pfd',
            ]
        );
    }

    public function providerForTestGenerate(): array
    {
        return [
            [['content', [], false], 'c'],
            [['content_version', [1, 2], true], 'ibx-c-1-v-2'],
            [['content_locations_with_parent_for_draft_suffix', [3], true], 'ibx-cl-3-pfd'],
        ];
    }

    public function providerForTestGenerateThrowsInvalidArgumentException(): array
    {
        return [
            [['test', [], false], 'c'],
            [['some_invalid_pattern', [1, 2], true], 'ibx-c-1-v-2'],
        ];
    }

    /**
     * @dataProvider providerForTestGenerate
     */
    public function testGenerate(array $arguments, string $resultKey): void
    {
        $this->assertEquals(
            $resultKey,
            $this->tagGenerator->generate(...$arguments)
        );
    }

    /**
     * @dataProvider providerForTestGenerateThrowsInvalidArgumentException
     */
    public function testGenerateThrowsInvalidArgumentException(array $arguments, string $resultKey): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->assertEquals(
            $resultKey,
            $this->tagGenerator->generate(...$arguments)
        );
    }
}
