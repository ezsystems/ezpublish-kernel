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
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\FloatStatsAggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\StatsAggregationResult;

final class FloatStatsAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield [
            new FloatStatsAggregation('float_stats', 'content_type', 'float_field_2'),
            new StatsAggregationResult(
                'float_stats',
                5,
                1.0,
                7.75,
                3.8,
                19.0
            ),
        ];
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
        $generator = new FieldAggregationFixtureGenerator($this->getRepository());
        $generator->setContentTypeIdentifier('content_type');
        $generator->setFieldDefinitionIdentifier('float_field_2');
        $generator->setFieldTypeIdentifier('ezfloat');
        $generator->setValues([1.0, 2.5, 2.5, 5.25, 7.75]);

        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
