<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Persistence;

use RuntimeException;
use SplFileInfo;

/**
 * Factory building an instance of Fixture depending on a file type.
 *
 * @see \eZ\Publish\SPI\Tests\Persistence\Fixture
 */
final class FileFixtureFactory
{
    public function buildFixture(string $filePath): Fixture
    {
        $fileInfo = new SplFileInfo($filePath);
        $extension = $fileInfo->getExtension();
        // note: there's no dependency injection available here, so using simple switch
        switch ($extension) {
            case 'yml':
            case 'yaml':
                return new YamlFixture($filePath);
            case 'php':
                return new PhpArrayFileFixture($filePath);
            default:
                throw new RuntimeException("Unsupported fixture file type: {$extension}");
        }
    }
}
