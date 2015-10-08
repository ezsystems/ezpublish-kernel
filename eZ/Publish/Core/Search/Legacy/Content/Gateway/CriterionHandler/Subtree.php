<?php

/**
 * File containing the DoctrineDatabase subtree criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Subtree criterion handler.
 */
class Subtree extends CriterionHandler
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
        return $criterion instanceof Criterion\Subtree;
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
        $table = $this->getUniqueTableName();

        $statements = array();
        foreach ($criterion->value as $pattern) {
            $statements[] = $query->expr->like(
                $this->dbHandler->quoteColumn('path_string', $table),
                $query->bindValue($pattern . '%')
            );
        }

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

        return $query->expr->lOr(
            $statements
        );
    }
}
