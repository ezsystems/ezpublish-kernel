<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

class IsUserEnabled extends CriterionHandler
{
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\IsUserEnabled;
    }

    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subSelect = $query->subSelect();
        $subSelect
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id', 't1')
            )->from(
                $query->alias(
                    $this->dbHandler->quoteTable('ezuser'),
                    't1'
                )
            )->leftJoin(
                $query->alias(
                    $this->dbHandler->quoteTable('ezuser_setting'),
                    't2'
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 't1'),
                    $this->dbHandler->quoteColumn('user_id', 't2')
                )
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_enabled', 't2'),
                    (int) reset($criterion->value)
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
