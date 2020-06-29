<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Filter\CriterionQueryBuilder\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Location\IdQueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Tests\Filter\BaseCriterionVisitorQueryBuilderTestCase;

/**
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Location\ParentLocationIdQueryBuilder::buildQueryConstraint
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Location\ParentLocationIdQueryBuilder::accepts
 */
final class LocationIdQueryBuilderTest extends BaseCriterionVisitorQueryBuilderTestCase
{
    public function getFilteringCriteriaQueryData(): iterable
    {
        yield 'Location ID=1' => [
            new Criterion\LocationId(1),
            'location.node_id IN (:dcValue1)',
            ['dcValue1' => [1]],
        ];

        yield 'Location ID=1 OR Location ID=2' => [
            new Criterion\LogicalOr(
                [
                    new Criterion\LocationId(1),
                    new Criterion\LocationId(2),
                ]
            ),
            '(location.node_id IN (:dcValue1)) OR (location.node_id IN (:dcValue2))',
            ['dcValue1' => [1], 'dcValue2' => [2]],
        ];
    }

    protected function getCriterionQueryBuilders(): iterable
    {
        return [new IdQueryBuilder()];
    }
}
