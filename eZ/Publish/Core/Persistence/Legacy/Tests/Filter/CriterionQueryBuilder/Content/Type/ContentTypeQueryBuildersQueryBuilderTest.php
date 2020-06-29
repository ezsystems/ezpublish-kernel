<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Filter\CriterionQueryBuilder\Content\Type;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\IdentifierQueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\IdQueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Tests\Filter\BaseCriterionVisitorQueryBuilderTestCase;

/**
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\IdentifierQueryBuilder::buildQueryConstraint
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\IdentifierQueryBuilder::accepts
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\IdQueryBuilder::buildQueryConstraint
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\IdQueryBuilder::accepts
 */
final class ContentTypeQueryBuildersQueryBuilderTest extends BaseCriterionVisitorQueryBuilderTestCase
{
    public function getFilteringCriteriaQueryData(): iterable
    {
        yield 'Content Type Identifier=article' => [
            new Criterion\ContentTypeIdentifier('article'),
            'content_type.identifier IN (:dcValue1)',
            ['dcValue1' => ['article']],
        ];

        yield 'Content Type ID=1' => [
            new Criterion\ContentTypeId(3),
            'content_type.id IN (:dcValue1)',
            ['dcValue1' => [3]],
        ];

        yield 'Content Type Identifier=folder OR Content Type ID IN (1, 2)' => [
            new Criterion\LogicalOr(
                [
                    new Criterion\ContentTypeIdentifier('folder'),
                    new Criterion\ContentTypeId([1, 2]),
                ]
            ),
            '(content_type.identifier IN (:dcValue1)) OR (content_type.id IN (:dcValue2))',
            ['dcValue1' => ['folder'], 'dcValue2' => [1, 2]],
        ];
    }

    protected function getCriterionQueryBuilders(): iterable
    {
        return [
            new IdentifierQueryBuilder(),
            new IdQueryBuilder(),
        ];
    }
}
