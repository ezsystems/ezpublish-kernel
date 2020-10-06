<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Aggregation;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;

abstract class AbstractTermAggregation implements Aggregation
{
    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_MIN_COUNT = 1;

    /**
     * The name of the aggregation.
     *
     * @var string
     */
    protected $name;

    /**
     * Number of facets (terms) returned.
     *
     * @var int
     */
    protected $limit = self::DEFAULT_LIMIT;

    /**
     * Specifies the minimum count. Only facet groups with more or equal results are returned.
     *
     * @var int
     */
    protected $minCount = self::DEFAULT_MIN_COUNT;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getMinCount(): int
    {
        return $this->minCount;
    }

    public function setMinCount(int $minCount): self
    {
        $this->minCount = $minCount;

        return $this;
    }
}
