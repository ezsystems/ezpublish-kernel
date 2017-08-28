<?php

/**
 * File containing the DoctrineDatabase content type group criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Content type group criterion handler.
 */
class ContentTypeGroupId extends CriterionHandler
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
        return $criterion instanceof Criterion\Matcher\ContentTypeGroupId;
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
        $subSelect = $query->subSelect();
        /** @var Criterion\Matcher\ContentTypeGroupId $criterion */
        $subSelect
            ->select(
                $this->dbHandler->quoteColumn('contentclass_id')
            )->from(
                $this->dbHandler->quoteTable('ezcontentclass_classgroup')
            )->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('group_id'),
                    $criterion->value
                )
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('contentclass_id', 'ezcontentobject'),
            $subSelect
        );
    }
}
