<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\Section;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionIdentifier;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * Section Identifier Filtering Criterion Query Builder.
 *
 * @internal for internal use by Repository Filtering
 */
final class IdentifierQueryBuilder implements CriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof SectionIdentifier;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        $queryBuilder
            ->joinOnce(
                'content',
                Gateway::CONTENT_SECTION_TABLE,
                'section',
                'content.section_id = section.id'
            );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionIdentifier $criterion */
        return $queryBuilder->expr()->in(
            'section.identifier',
            $queryBuilder->createNamedParameter(
                (array)$criterion->value,
                Connection::PARAM_STR_ARRAY
            )
        );
    }
}
