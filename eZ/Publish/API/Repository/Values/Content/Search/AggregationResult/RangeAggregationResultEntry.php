<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\ValueObject;

final class RangeAggregationResultEntry extends ValueObject
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range */
    private $key;

    /** @var int */
    private $count;

    public function __construct(Range $key, int $count)
    {
        parent::__construct();

        $this->key = $key;
        $this->count = $count;
    }

    public function getKey(): Range
    {
        return $this->key;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
