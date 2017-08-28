<?php

/**
 * File containing the DoctrineDatabase location main status criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\CriterionHandler\Location;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Location main status criterion handler.
 */
class IsMainLocation extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriterionInterface $criterion
     *
     * @return bool
     */
    public function accept(CriterionInterface $criterion)
    {
        return $criterion instanceof Criterion\Matcher\Location\IsMainLocation;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param CriterionInterface $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        CriterionInterface $criterion,
        array $languageSettings
    ) {
        $idColumn = $this->dbHandler->quoteColumn('node_id', 'ezcontentobject_tree');
        $mainIdColumn = $this->dbHandler->quoteColumn('main_node_id', 'ezcontentobject_tree');

        /** @var Criterion\Matcher\Location\IsMainLocation $criterion */
        switch ($criterion->value[0]) {
            case Criterion\Matcher\Location\IsMainLocation::MAIN:
                return $query->expr->eq(
                    $idColumn,
                    $mainIdColumn
                );

            case Criterion\Matcher\Location\IsMainLocation::NOT_MAIN:
                return $query->expr->neq(
                    $idColumn,
                    $mainIdColumn
                );

            default:
                throw new RuntimeException(
                    "Unknown value '{$criterion->value[0]}' for IsMainLocation criterion handler."
                );
        }
    }
}
