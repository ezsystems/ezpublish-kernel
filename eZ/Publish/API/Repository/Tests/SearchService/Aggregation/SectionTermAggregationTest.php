<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\DataSetBuilder\TermAggregationDataSetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\SectionTermAggregation;

final class SectionTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        $aggregation = new SectionTermAggregation('section');

        $builder = new TermAggregationDataSetBuilder($aggregation);
        $builder->setExpectedEntries([
            'users' => 8,
            'media' => 4,
            'standard' => 2,
            'setup' => 2,
            'design' => 2,
        ]);

        $builder->setEntryMapper([
            $this->getRepository()->getSectionService(),
            'loadSectionByIdentifier',
        ]);

        yield $builder->build();
    }
}
