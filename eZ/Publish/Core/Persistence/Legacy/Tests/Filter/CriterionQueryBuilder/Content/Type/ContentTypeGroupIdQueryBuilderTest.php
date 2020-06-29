<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Filter\CriterionQueryBuilder\Content\Type;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeGroupId;
use eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\GroupIdQueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Tests\Filter\BaseCriterionVisitorQueryBuilderTestCase;

/**
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\GroupIdQueryBuilder::buildQueryConstraint
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Type\GroupIdQueryBuilder::accepts
 */
class ContentTypeGroupIdQueryBuilderTest extends BaseCriterionVisitorQueryBuilderTestCase
{
    public function getFilteringCriteriaQueryData(): iterable
    {
        yield 'Content Type Group ID=1' => [
            new ContentTypeGroupId(1),
            'content_type_group.id IN (:dcValue1)',
            ['dcValue1' => [1]],
        ];

        yield 'Content Type Group ID IN (1, 2)' => [
            new ContentTypeGroupId([1, 2]),
            'content_type_group.id IN (:dcValue1)',
            ['dcValue1' => [1, 2]],
        ];
    }

    protected function getCriterionQueryBuilders(): iterable
    {
        return [new GroupIdQueryBuilder()];
    }
}
