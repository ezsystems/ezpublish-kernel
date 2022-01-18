<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Core\IO\FilePathNormalizer;

use eZ\Publish\Core\IO\FilePathNormalizer\Flysystem;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;
use PHPUnit\Framework\TestCase;

final class FlysystemTest extends TestCase
{
    /** @var \eZ\Publish\Core\IO\FilePathNormalizer\Flysystem */
    private $filePathNormalizer;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter|\PHPUnit\Framework\MockObject\MockObject */
    private $slugConverter;

    public function setUp(): void
    {
        $this->slugConverter = $this->createMock(SlugConverter::class);
        $this->filePathNormalizer = new Flysystem($this->slugConverter);
    }

    /**
     * @dataProvider providerForTestNormalizePath
     */
    public function testNormalizePath(
        string $originalPath,
        string $fileName,
        string $sluggedFileName,
        string $regex
    ): void {
        $this->slugConverter
            ->expects(self::once())
            ->method('convert')
            ->with($fileName)
            ->willReturn($sluggedFileName);

        $normalizedPath = $this->filePathNormalizer->normalizePath($originalPath);

        self::assertStringEndsWith($sluggedFileName, $normalizedPath);
        self::assertRegExp($regex, $normalizedPath);
    }

    public function providerForTestNormalizePath(): array
    {
        $defaultPattern = '/\/[0-9a-f]{12}-';

        return [
            'No special chars' => [
                '4/3/2/234/1/image.jpg',
                'image.jpg',
                'image.jpg',
                $defaultPattern . 'image.jpg/',
            ],
            'Spaces in the filename' => [
                '4/3/2/234/1/image with spaces.jpg',
                'image with spaces.jpg',
                'image-with-spaces.jpg',
                $defaultPattern . 'image-with-spaces.jpg/',
            ],
            'Encoded spaces in the name' => [
                '4/3/2/234/1/image%20+no+spaces.jpg',
                'image%20+no+spaces.jpg',
                'image-20-nospaces.jpg',
                $defaultPattern . 'image-20-nospaces.jpg/',
            ],
            'Special chars in the name' => [
                '4/3/2/234/1/image%20+no+spaces?.jpg',
                'image%20+no+spaces?.jpg',
                'image-20-nospaces.jpg',
                $defaultPattern . 'image-20-nospaces.jpg/',
            ],
            'Already hashed name' => [
                '4/3/2/234/1/14ff44718877-hashed.jpg',
                '14ff44718877-hashed.jpg',
                '14ff44718877-hashed.jpg',
                '/^4\/3\/2\/234\/1\/14ff44718877-hashed.jpg$/',
            ],
        ];
    }
}
