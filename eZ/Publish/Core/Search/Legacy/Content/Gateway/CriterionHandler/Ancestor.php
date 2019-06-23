<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Visits the Ancestor criterion.
 */
class Ancestor extends CriterionHandler
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
        return $criterion instanceof Criterion\Ancestor;
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
        $idSet = [];
        foreach ($criterion->value as $value) {
            foreach (explode('/', trim($value, '/')) as $id) {
                $idSet[$id] = true;
            }
        }

        $subSelect = $query->subSelect();
        $subSelect
            ->select($this->dbHandler->quoteColumn('contentobject_id'))
            ->from($this->dbHandler->quoteTable('ezcontentobject_tree'))
            ->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('node_id'),
                    array_keys($idSet)
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
