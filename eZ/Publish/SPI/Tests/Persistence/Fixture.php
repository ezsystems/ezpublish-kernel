<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Persistence;

/**
 * Represents database fixture.
 *
 * @internal for internal use by Repository test setup
 */
interface Fixture
{
    /**
     * Load database fixture into a map of table names to table rows data.
     *
     * @return array
     */
    public function load(): array;
}
