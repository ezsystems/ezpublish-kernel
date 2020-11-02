<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation\Field;

use DateTime;
use DateTimeZone;
use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\AbstractAggregationTest;
use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\FixtureGenerator\FieldAggregationFixtureGenerator;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\DateTimeRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;

final class DateTimeRangeAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        $timezone = new DateTimeZone('+0000');

        yield [
            new DateTimeRangeAggregation(
                'datetime_range',
                'content_type',
                'datetime_field',
                [
                    new Range(
                        null,
                        new DateTime('2020-06-30 00:00:01', $timezone)
                    ),
                    new Range(
                        new DateTime('2020-06-30 12:00:00', $timezone),
                        new DateTime('2020-07-30 00:00:00', $timezone)
                    ),
                    new Range(
                        new DateTime('2020-07-30 00:00:01', $timezone),
                        new DateTime('2020-08-01 00:00:03', $timezone)
                    ),
                ]
            ),
            new RangeAggregationResult(
                'datetime_range',
                [
                    new RangeAggregationResultEntry(
                        new Range(
                            null,
                            new DateTime('2020-06-30 00:00:01', $timezone)
                        ),
                        2,
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2020-06-30 12:00:00', $timezone),
                            new DateTime('2020-07-30 00:00:00', $timezone)
                        ),
                        3
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2020-07-30 00:00:01', $timezone),
                            new DateTime('2020-08-01 00:00:03', $timezone)
                        ),
                        3
                    ),
                ]
            ),
        ];
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
        $timezone = new DateTimeZone('+0000');

        $generator = new FieldAggregationFixtureGenerator($this->getRepository());
        $generator->setContentTypeIdentifier('content_type');
        $generator->setFieldDefinitionIdentifier('datetime_field');
        $generator->setFieldTypeIdentifier('ezdatetime');
        $generator->setValues([
            new DateTime('2020-05-01 00:00:00', $timezone),
            new DateTime('2020-06-30 00:00:00', $timezone),
            new DateTime('2020-06-30 12:00:00', $timezone),
            new DateTime('2020-07-01 00:00:00', $timezone),
            new DateTime('2020-07-01 12:00:00', $timezone),
            new DateTime('2020-07-30 00:00:00', $timezone),
            new DateTime('2020-07-30 12:00:00', $timezone),
            new DateTime('2020-08-01 00:00:01', $timezone),
            new DateTime('2020-08-01 00:00:02', $timezone),
            new DateTime('2020-08-01 00:00:03', $timezone),
        ]);

        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
