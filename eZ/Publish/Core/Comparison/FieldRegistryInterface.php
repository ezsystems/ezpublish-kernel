<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Comparison;

use eZ\Publish\SPI\FieldType\Comparable;

interface FieldRegistryInterface
{
    public function registerType(string $name, Comparable $type): void;

    public function getType(string $name): Comparable;
}
