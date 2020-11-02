<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation\DataSetBuilder;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;

/**
 * @internal
 */
final class TermAggregationDataSetBuilder
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Query\Aggregation */
    private $aggregation;

    /** @var array */
    private $entries;

    /** @var callable|null */
    private $mapper;

    public function __construct(Aggregation $aggregation)
    {
        $this->aggregation = $aggregation;
        $this->entries = [];
        $this->mapper = null;
    }

    public function setExpectedEntries(array $entries): self
    {
        $this->entries = $entries;

        return $this;
    }

    public function setEntryMapper(callable $mapper): self
    {
        $this->mapper = $mapper;

        return $this;
    }

    public function build(): array
    {
        return [
            $this->aggregation,
            $this->buildExpectedTermAggregationResult(),
        ];
    }

    private function buildExpectedTermAggregationResult(): TermAggregationResult
    {
        $entries = [];
        foreach ($this->entries as $key => $count) {
            if ($this->mapper !== null) {
                $key = ($this->mapper)($key);
            }

            $entries[] = new TermAggregationResultEntry($key, $count);
        }

        return TermAggregationResult::createForAggregation($this->aggregation, $entries);
    }
}
