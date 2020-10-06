<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;

use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;

final class StatsAggregationResult extends AggregationResult
{
    /** @var float|null */
    public $sum;

    /** @var int|null */
    private $count;

    /** @var float|null */
    private $min;

    /** @var float|null */
    private $max;

    /** @var float|null */
    private $avg;

    public function __construct(string $name, ?int $count, ?float $min, ?float $max, ?float $avg, ?float $sum)
    {
        parent::__construct($name);

        $this->count = $count;
        $this->min = $min;
        $this->max = $max;
        $this->avg = $avg;
        $this->sum = $sum;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function getMax(): ?float
    {
        return $this->max;
    }

    public function getAvg(): ?float
    {
        return $this->avg;
    }

    public function getSum(): ?float
    {
        return $this->sum;
    }
}
