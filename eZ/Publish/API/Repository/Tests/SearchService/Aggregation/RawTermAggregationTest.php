<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\RawTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;

final class RawTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield [
            new RawTermAggregation(
                'raw_term',
                'content_section_identifier_id'
            ),
            new TermAggregationResult('raw_term', [
                new TermAggregationResultEntry('users', 8),
                new TermAggregationResultEntry('media', 4),
                new TermAggregationResultEntry('design', 2),
                new TermAggregationResultEntry('setup', 2),
                new TermAggregationResultEntry('standard', 2),
            ]),
        ];
    }
}
