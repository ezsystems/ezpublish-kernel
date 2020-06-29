<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Persistence\Filter\SortClauseVisitor as FilteringSortClauseVisitor;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringSortClause;
use eZ\Publish\SPI\Repository\Values\Filter\SortClauseQueryBuilder;

/**
 * @internal Type-hint {@see \eZ\Publish\SPI\Persistence\Filter\SortClauseVisitor} instead.
 */
final class SortClauseVisitor implements FilteringSortClauseVisitor
{
    /** @var \eZ\Publish\SPI\Repository\Values\Filter\SortClauseQueryBuilder[] */
    private $sortClauseQueryBuilders;

    /** @var \eZ\Publish\SPI\Repository\Values\Filter\SortClauseQueryBuilder[] */
    private static $queryBuildersForSortClauses = [];

    public function __construct(iterable $sortClauseQueryBuilders)
    {
        $this->sortClauseQueryBuilders = $sortClauseQueryBuilders;
    }

    /**
     * @param \eZ\Publish\SPI\Repository\Values\Filter\FilteringSortClause[] $sortClauses
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException if there's no builder for a Sort Clause
     */
    public function visitSortClauses(FilteringQueryBuilder $queryBuilder, array $sortClauses): void
    {
        foreach ($sortClauses as $sortClause) {
            $this
                ->getQueryBuilderForSortClause($sortClause)
                ->buildQuery($queryBuilder, $sortClause);
        }
    }

    /**
     * Cache Query Builders in-memory and get the one for the given Sort Clause.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    private function getQueryBuilderForSortClause(
        FilteringSortClause $sortClause
    ): SortClauseQueryBuilder {
        $sortClauseFQCN = get_class($sortClause);
        if (!isset(self::$queryBuildersForSortClauses[$sortClauseFQCN])) {
            foreach ($this->sortClauseQueryBuilders as $sortClauseQueryBuilder) {
                if ($sortClauseQueryBuilder->accepts($sortClause)) {
                    self::$queryBuildersForSortClauses[$sortClauseFQCN] = $sortClauseQueryBuilder;
                    break;
                }
            }
        }

        if (!isset(self::$queryBuildersForSortClauses[$sortClauseFQCN])) {
            throw new NotImplementedException(
                "There are no Query Builders for {$sortClauseFQCN} Sort Clause"
            );
        }

        return self::$queryBuildersForSortClauses[$sortClauseFQCN];
    }
}
