<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * Content Language Code Criterion visitor query builder.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode
 *
 * @internal for internal use by Repository Filtering
 */
final class LanguageCodeQueryBuilder implements CriterionQueryBuilder
{
    public function accepts(FilteringCriterion $criterion): bool
    {
        return $criterion instanceof LanguageCode;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode $criterion */
        $queryBuilder
            ->joinOnce(
                'version',
                Gateway::CONTENT_LANGUAGE_TABLE,
                'language',
                // bitwise and for exact language ID match
                'language.id & version.language_mask = language.id'
            );

        // at this point $criterion->value is guaranteed to be an array
        $expr = $queryBuilder->expr()->in(
            'language.locale',
            $queryBuilder->createNamedParameter(
                $criterion->value,
                Connection::PARAM_STR_ARRAY
            )
        );

        if ($criterion->matchAlwaysAvailable) {
            $expr = (string)$queryBuilder->expr()->orX($expr, 'version.language_mask & 1 = 1');
        }

        return $expr;
    }
}
