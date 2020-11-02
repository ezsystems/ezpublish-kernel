<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use DateTime;
use DateTimeZone;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;

final class DateMetadataRangeAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        $timezone = new DateTimeZone('+0000');

        yield '::MODIFIED' => [
            new DateMetadataRangeAggregation(
                'modification_date',
                DateMetadataRangeAggregation::MODIFIED,
                [
                    new Range(
                        null,
                        new DateTime('2003-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2003-01-01', $timezone),
                        new DateTime('2004-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2004-01-01', $timezone),
                        null
                    ),
                ]
            ),
            new RangeAggregationResult(
                'modification_date',
                [
                    new RangeAggregationResultEntry(
                        new Range(
                            null,
                            new DateTime('2003-01-01', $timezone)
                        ),
                        3
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2003-01-01', $timezone),
                            new DateTime('2004-01-01', $timezone)
                        ),
                        3
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2004-01-01', $timezone),
                            null
                        ),
                        12
                    ),
                ]
            ),
        ];

        yield '::PUBLISHED' => [
            new DateMetadataRangeAggregation(
                'publication_date',
                DateMetadataRangeAggregation::PUBLISHED,
                [
                    new Range(
                        null,
                        new DateTime('2003-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2003-01-01', $timezone),
                        new DateTime('2004-01-01', $timezone)
                    ),
                    new Range(
                        new DateTime('2004-01-01', $timezone),
                        null
                    ),
                ]
            ),
            new RangeAggregationResult(
                'publication_date',
                [
                    new RangeAggregationResultEntry(
                        new Range(
                            null,
                            new DateTime('2003-01-01', $timezone)
                        ),
                        6
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2003-01-01', $timezone),
                            new DateTime('2004-01-01', $timezone)
                        ),
                        2
                    ),
                    new RangeAggregationResultEntry(
                        new Range(
                            new DateTime('2004-01-01', $timezone),
                            null
                        ),
                        10
                    ),
                ]
            ),
        ];
    }
}
