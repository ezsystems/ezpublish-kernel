<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\CriterionHandler\Location;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * Location main status criterion handler.
 */
class IsMainLocation extends CriterionHandler
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
        return $criterion instanceof Criterion\Location\IsMainLocation;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $idColumn = 't.node_id';
        $mainIdColumn = 't.main_node_id';

        switch ($criterion->value[0]) {
            case Criterion\Location\IsMainLocation::MAIN:
                return $queryBuilder->expr()->eq(
                    $idColumn,
                    $mainIdColumn
                );

            case Criterion\Location\IsMainLocation::NOT_MAIN:
                return $queryBuilder->expr()->neq(
                    $idColumn,
                    $mainIdColumn
                );

            default:
                throw new RuntimeException(
                    "Unknown value '{$criterion->value[0]}' for IsMainLocation Criterion handler."
                );
        }
    }
}
