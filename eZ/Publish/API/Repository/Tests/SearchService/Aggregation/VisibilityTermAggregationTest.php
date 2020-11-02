<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\DataSetBuilder\TermAggregationDataSetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\VisibilityTermAggregation;

final class VisibilityTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        $aggregation = new VisibilityTermAggregation('visibility');

        $builder = new TermAggregationDataSetBuilder($aggregation);
        $builder->setExpectedEntries([
            true => 18,
        ]);

        yield $builder->build();
    }
}
