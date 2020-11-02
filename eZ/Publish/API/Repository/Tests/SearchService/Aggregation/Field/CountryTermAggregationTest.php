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
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

final class CountryTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield '::TYPE_NAME' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_NAME
            ),
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry('Canada', 4),
                    new TermAggregationResultEntry('France', 3),
                    new TermAggregationResultEntry('Poland', 2),
                    new TermAggregationResultEntry('Belgium', 1),
                    new TermAggregationResultEntry('Gabon', 1),
                ]
            ),
        ];

        yield '::TYPE_ALPHA_2' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_ALPHA_2
            ),
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry('CA', 4),
                    new TermAggregationResultEntry('FR', 3),
                    new TermAggregationResultEntry('PL', 2),
                    new TermAggregationResultEntry('BE', 1),
                    new TermAggregationResultEntry('GA', 1),
                ]
            ),
        ];

        yield '::TYPE_ALPHA_3' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_ALPHA_3
            ),
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry('CAN', 4),
                    new TermAggregationResultEntry('FRA', 3),
                    new TermAggregationResultEntry('POL', 2),
                    new TermAggregationResultEntry('BEL', 1),
                    new TermAggregationResultEntry('GAB', 1),
                ]
            ),
        ];

        yield '::TYPE_IDC' => [
            new CountryTermAggregation(
                'country_term',
                'content_type',
                'country',
                CountryTermAggregation::TYPE_IDC
            ),
            new TermAggregationResult(
                'country_term',
                [
                    new TermAggregationResultEntry(1, 4),
                    new TermAggregationResultEntry(33, 3),
                    new TermAggregationResultEntry(48, 2),
                    new TermAggregationResultEntry(32, 1),
                    new TermAggregationResultEntry(241, 1),
                ]
            ),
        ];
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
        $generator = new FieldAggregationFixtureGenerator($this->getRepository());
        $generator->setContentTypeIdentifier('content_type');
        $generator->setFieldDefinitionIdentifier('country');
        $generator->setFieldTypeIdentifier('ezcountry');
        $generator->setValues([
            ['PL', 'US'],
            ['FR', 'US'],
            ['US'],
            ['GA', 'PL', 'FR'],
            ['FR', 'BE', 'US'],
        ]);

        $generator->setFieldDefinitionCreateStructConfigurator(
            static function (FieldDefinitionCreateStruct $createStruct): void {
                $createStruct->fieldSettings = [
                    'isMultiple' => true,
                ];
            },
        );

        $generator->execute();

        $this->refreshSearch($this->getRepository());
    }
}
