<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

use Doctrine\DBAL\ParameterType;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;

class VisibleOnly extends Base
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\VisibleOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion)
    {
        $this->joinContentObjectLink($query);
        $this->joinContentObjectAttribute($query);

        $currentQuery = $query->getQuery();
        if (strpos($currentQuery, 'INNER JOIN ezcontentobject_tree') === false) {
            $query->innerJoin('ezcontentobject_tree', $query->expr->lAnd(
                $query->expr->eq(
                    'ezcontentobject_tree.contentobject_id',
                    'ezcontentobject_attribute.contentobject_id'
                ),
                $query->expr->eq(
                    'ezcontentobject_tree.contentobject_version',
                    'ezcontentobject_attribute.version'
                )
            ));
        }

        return $query->expr->eq(
            'ezcontentobject_tree.is_invisible',
            $query->bindValue(0, null, ParameterType::INTEGER)
        );
    }
}
