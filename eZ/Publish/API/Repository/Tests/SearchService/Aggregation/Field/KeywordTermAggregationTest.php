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
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\KeywordTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;

final class KeywordTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield [
            new KeywordTermAggregation(
                'keyword_term',
                'content_type',
                'keyword_field'
            ),
            new TermAggregationResult(
                'keyword_term',
                [
                    new TermAggregationResultEntry('foo', 3),
                    new TermAggregationResultEntry('bar', 2),
                    new TermAggregationResultEntry('baz', 1),
                ]
            ),
        ];
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
        $generator = new FieldAggregationFixtureGenerator($this->getRepository());
        $generator->setContentTypeIdentifier('content_type');
        $generator->setFieldDefinitionIdentifier('keyword_field');
        $generator->setFieldTypeIdentifier('ezkeyword');
        $generator->setValues([
            ['foo'],
            ['foo', 'bar'],
            ['foo', 'bar', 'baz'],
        ]);

        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
