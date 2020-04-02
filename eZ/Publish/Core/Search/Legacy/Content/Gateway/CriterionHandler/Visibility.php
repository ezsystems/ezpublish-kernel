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

/**
 * Visibility criterion handler.
 */
class Visibility extends CriterionHandler
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
        return $criterion instanceof Criterion\Visibility;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $subSelect = $this->connection->createQueryBuilder();

        if ($criterion->value[0] === Criterion\Visibility::VISIBLE) {
            $expression = $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq(
                    'subquery_location.is_hidden',
                    0
                ),
                $queryBuilder->expr()->eq(
                    'subquery_location.is_invisible',
                    0
                )
            );
        } else {
            $expression = $queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq(
                    'subquery_location.is_hidden',
                    1
                ),
                $queryBuilder->expr()->eq(
                    'subquery_location.is_invisible',
                    1
                )
            );
        }

        $subSelect
            ->select('contentobject_id')
            ->from(LocationGateway::CONTENT_TREE_TABLE, 'subquery_location')
            ->where($expression);

        return $queryBuilder->expr()->in(
            'c.id',
            $subSelect->getSQL()
        );
    }
}
