<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway as SectionGateway;
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
    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion
    ) {
        $this->joinContentObjectLink($queryBuilder);
        $this->joinContentObjectAttribute($queryBuilder);
        $this->joinContentObject($queryBuilder);

        $queryBuilder->innerJoin(
            'c',
            SectionGateway::CONTENT_SECTION_TABLE,
            's',
            'c.section_id = s.id'
        );

        return $queryBuilder->expr()->in(
            's.identifier',
            $queryBuilder->createNamedParameter(
                $criterion->sectionIdentifiers,
                Connection::PARAM_STR_ARRAY,
                ':section_identifiers'
            )
        );
    }
}
