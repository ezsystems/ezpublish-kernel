<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;

use ArrayIterator;
use Countable;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;
use Iterator;
use IteratorAggregate;

class TermAggregationResult extends AggregationResult implements IteratorAggregate, Countable
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry[] */
    private $entries;

    public function __construct(string $name, iterable $entries = [])
    {
        parent::__construct($name);

        $this->entries = $entries;
    }

    public function isEmpty(): bool
    {
        return empty($this->entries);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry[]
     */
    public function getEntries(): iterable
    {
        return $this->entries;
    }

    /**
     * @param object|string|int $key
     */
    public function getEntry($key): ?TermAggregationResultEntry
    {
        foreach ($this->entries as $entry) {
            if ($entry->getKey() == $key) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @param object|string|int $key
     */
    public function hasEntry($key): bool
    {
        return $this->getEntry($key) !== null;
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function getIterator(): Iterator
    {
        if (empty($this->entries)) {
            return new ArrayIterator();
        }

        foreach ($this->entries as $entry) {
            yield $entry->getKey() => $entry->getCount();
        }
    }

    public static function createForAggregation(Aggregation $aggregation, iterable $entries = []): self
    {
        return new self($aggregation->getName(), $entries);
    }
}
