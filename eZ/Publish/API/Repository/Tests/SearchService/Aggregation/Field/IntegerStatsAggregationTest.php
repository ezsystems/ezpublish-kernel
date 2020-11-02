<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation\Field;

use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\AbstractAggregationTest;
use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\FixtureGenerator\FieldAggregationFixtureGenerator;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\IntegerStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\StatsAggregationResult;

final class IntegerStatsAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield [
            new IntegerStatsAggregation('integer_stats', 'content_type', 'integer_field'),
            new StatsAggregationResult(
                'integer_stats',
                7,
                1,
                21,
                7.571428571428571,
                53
            ),
        ];
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
        $generator = new FieldAggregationFixtureGenerator($this->getRepository());
        $generator->setContentTypeIdentifier('content_type');
        $generator->setFieldDefinitionIdentifier('integer_field');
        $generator->setFieldTypeIdentifier('ezinteger');
        $generator->setValues([1, 2, 3, 5, 8, 13, 21]);
        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
