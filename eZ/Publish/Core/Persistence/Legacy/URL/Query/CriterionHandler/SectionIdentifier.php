<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;

class SectionIdentifier extends Base
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\SectionIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion): string
    {
        $this->joinContentObjectLink($query);
        $this->joinContentObjectAttribute($query);
        $this->joinContentObject($query);

        if (strpos($query->getQuery(), 'INNER JOIN ezsection ') === false) {
            $query->innerJoin(
                'ezsection',
                $query->expr->eq('ezcontentobject.section_id', 'ezsection.id')
            );
        }

        return $query->expr->in('ezsection.identifier', $criterion->sectionIdentifiers);
    }
}
