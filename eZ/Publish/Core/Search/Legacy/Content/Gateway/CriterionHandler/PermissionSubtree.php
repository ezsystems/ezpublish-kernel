<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway\CriterionHandler;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\Values\Content\Query\Criterion\PermissionSubtree as PermissionSubtreeCriterion;

/**
 * PermissionSubtree criterion handler.
 */
class PermissionSubtree extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof PermissionSubtreeCriterion;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $table = 'permission_subtree';

        $statements = [];
        foreach ($criterion->value as $pattern) {
            $statements[] = $queryBuilder->expr()->like(
                "{$table}.path_string",
                $queryBuilder->createNamedParameter($pattern . '%')
            );
        }

        $locationTableAlias = $this->connection->quoteIdentifier($table);
        if (!$this->hasJoinedTableAs($queryBuilder, $locationTableAlias)) {
            $queryBuilder
                ->leftJoin(
                    'c',
                    LocationGateway::CONTENT_TREE_TABLE,
                    $locationTableAlias,
                    $queryBuilder->expr()->eq(
                        "{$locationTableAlias}.contentobject_id",
                        'c.id'
                    )
                );
        }

        return $queryBuilder->expr()->orX(...$statements);
    }
}
