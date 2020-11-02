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
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\SelectionTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

final class SelectionTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield [
            new SelectionTermAggregation(
                'selection_term',
                'content_type',
                'selection_field'
            ),
            new TermAggregationResult(
                'selection_term',
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
        $generator->setFieldDefinitionIdentifier('selection_field');
        $generator->setFieldTypeIdentifier('ezselection');
        $generator->setValues([
            [0],
            [0, 1],
            [0, 1, 2],
        ]);

        $generator->setFieldDefinitionCreateStructConfigurator(
            static function (FieldDefinitionCreateStruct $createStruct): void {
                $createStruct->fieldSettings = [
                    'isMultiple' => true,
                    'options' => [
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'baz',
                    ],
                ];
            },
        );

        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
