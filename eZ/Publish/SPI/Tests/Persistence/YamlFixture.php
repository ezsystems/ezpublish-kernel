<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Persistence;

use Symfony\Component\Yaml\Yaml;

/**
 * Data fixture stored in Yaml file.
 *
 * @internal for internal use by Repository test setup
 */
final class YamlFixture extends BaseInMemoryCachedFileFixture
{
    protected function loadFixture(): array
    {
        return Yaml::parseFile($this->getFilePath());
    }
}
