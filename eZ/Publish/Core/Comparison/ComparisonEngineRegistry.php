<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Comparison;

use eZ\Publish\SPI\Comparison\ComparisonEngine;
use eZ\Publish\SPI\Comparison\Field\NoComparison;

final class ComparisonEngineRegistry
{
    /** @var \eZ\Publish\SPI\Comparison\ComparisonEngine[] */
    private $engines = [];

    /**
     * @param \eZ\Publish\SPI\Comparison\ComparisonEngine[] $engines
     */
    public function __construct(array $engines = [])
    {
        foreach ($engines as $supportedType => $engine) {
            $this->registerEngine($supportedType, $engine);
        }
    }

    public function registerEngine(string $supportedType, ComparisonEngine $engine): void
    {
        $this->engines[$supportedType] = $engine;
    }

    public function getEngine(string $supportedType): ComparisonEngine
    {
        if (!isset($this->engines[$supportedType])) {
            return $this->engines[NoComparison::class];
        }

        return $this->engines[$supportedType];
    }
}
