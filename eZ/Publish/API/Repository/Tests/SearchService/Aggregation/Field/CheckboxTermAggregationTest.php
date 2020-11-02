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
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\CheckboxTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use eZ\Publish\Core\FieldType\Checkbox\Value as CheckboxValue;

final class CheckboxTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield [
            new CheckboxTermAggregation('checkbox_term', 'content_type', 'boolean'),
            new TermAggregationResult(
                'checkbox_term',
                [
                    new TermAggregationResultEntry(true, 3),
                    new TermAggregationResultEntry(false, 2),
                ]
            ),
        ];
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
        $generator = new FieldAggregationFixtureGenerator($this->getRepository());
        $generator->setContentTypeIdentifier('content_type');
        $generator->setFieldDefinitionIdentifier('boolean');
        $generator->setFieldTypeIdentifier('ezboolean');
        $generator->setValues([
            new CheckboxValue(true),
            new CheckboxValue(true),
            new CheckboxValue(true),
            new CheckboxValue(false),
            new CheckboxValue(false),
        ]);

        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
