<?php

/**
 * File containing the DoctrineDatabase permission subtree criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
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

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $table = 'permission_subtree';

        $statements = [];
        foreach ($criterion->value as $pattern) {
            $statements[] = $query->expr->like(
                $this->dbHandler->quoteColumn('path_string', $table),
                $query->bindValue($pattern . '%')
            );
        }

        // Check if ezcontentobject_tree was already joined, if it was there is no need to join
        // with it again - first join will be reused by all other PermissionSubtree criteria
        /** @var $query \eZ\Publish\Core\Persistence\Doctrine\SelectDoctrineQuery */
        if (!$query->permissionSubtreeJoinAdded) {
            $query
                ->leftJoin(
                    $query->alias(
                        $this->dbHandler->quoteTable('ezcontentobject_tree'),
                        $this->dbHandler->quoteIdentifier($table)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentobject_id', $table),
                        $this->dbHandler->quoteColumn('id', 'ezcontentobject')
                    )
                );

            // Set joined state to true
            $query->permissionSubtreeJoinAdded = true;
        }

        return $query->expr->lOr(
            $statements
        );
    }
}
