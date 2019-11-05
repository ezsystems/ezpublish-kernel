<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Query;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Interface CriterionHandler
 *
 * Expects to get a DBAL QueryBuilder where the following tables are defined already (as from or as some form of join)
 * - "location", an alias of ezcontentobject_tree or ezcontentobject_trash table
 *     - In content query this will be left join with main location only
 *     - In location/trash query this will be from table, while content will be by inner join
 * - "content", alias for ezcontentobject table)
 *
 * TODO: Double check that use of main location as left join will work like in content search.
 */
interface CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion): bool;

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Query\CriteriaConverter $converter For use by LogicalOperators.
     * @param \Doctrine\DBAL\Query\QueryBuilder $query Should only be used to:
     *         1. Set parameters if needed using {@see QueryBuilder::createNamedParameter()}.
     *         2. Add join, *if needed* by checking with {@see QueryBuilder::getQueryPart()}.
     *         3. Get expression object using {@see QueryBuilder::expr()} and return result of that.
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression|string Expression to be used in where clause of query
     */
    public function handle(CriteriaConverter $converter, QueryBuilder $query, Criterion $criterion);
}
