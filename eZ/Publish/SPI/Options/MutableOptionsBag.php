<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Options;

interface MutableOptionsBag extends OptionsBag
{
    /**
     * @param mixed|null $value
     */
    public function set(string $key, $value): void;

    public function remove(string $key): void;
}
