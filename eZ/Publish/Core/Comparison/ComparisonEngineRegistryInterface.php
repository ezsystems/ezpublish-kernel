<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Comparison;

use eZ\Publish\SPI\Comparison\ComparisonEngine;

interface ComparisonEngineRegistryInterface
{
    public function registerEngine(string $supportedType, ComparisonEngine $engine): void;

    public function getEngine(string $supportedType): ComparisonEngine;
}
