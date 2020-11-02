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
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\IntegerRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;

final class IntegerRangeAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield [
            new IntegerRangeAggregation('integer_range', 'content_type', 'integer_field', [
                new Range(null, 10),
                new Range(10, 25),
                new Range(25, 50),
                new Range(50, null),
            ]),
            new RangeAggregationResult(
                'integer_range',
                [
                    new RangeAggregationResultEntry(new Range(null, 10), 9),
                    new RangeAggregationResultEntry(new Range(10, 25), 15),
                    new RangeAggregationResultEntry(new Range(25, 50), 25),
                    new RangeAggregationResultEntry(new Range(50, null), 51),
                ]
            ),
        ];
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
        $generator = new FieldAggregationFixtureGenerator($this->getRepository());
        $generator->setContentTypeIdentifier('content_type');
        $generator->setFieldDefinitionIdentifier('integer_field');
        $generator->setFieldTypeIdentifier('ezinteger');
        $generator->setValues(range(1, 100));

        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
