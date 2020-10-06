<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Aggregation;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;

abstract class AbstractRangeAggregation implements Aggregation
{
    /**
     * The name of the aggregation.
     *
     * @var string
     */
    protected $name;

    /** @var \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range[] */
    protected $ranges;

    public function __construct(string $name, array $ranges = [])
    {
        $this->name = $name;
        $this->ranges = $ranges;
    }

    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
