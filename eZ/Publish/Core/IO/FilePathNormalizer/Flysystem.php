<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\IO\FilePathNormalizer;

use eZ\Publish\Core\IO\FilePathNormalizerInterface;
use League\Flysystem\Util;

final class Flysystem implements FilePathNormalizerInterface
{
    public function normalizePath(string $filePath): string
    {
        return Util::normalizePath($filePath);
    }
}
