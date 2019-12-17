<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Persistence;

/**
 * Data fixture stored in PHP file which returns it as an array.
 *
 * @internal for internal use by Repository test setup
 */
class PhpArrayFileFixture extends BaseInMemoryCachedFileFixture
{
    protected function loadFixture(): array
    {
        return require $this->getFilePath();
    }
}
