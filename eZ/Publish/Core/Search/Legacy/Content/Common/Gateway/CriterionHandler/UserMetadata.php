<?php

/**
 * File containing the DoctrineDatabase user metadata criterion handler class.
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
use RuntimeException;

/**
 * User metadata criterion handler.
 */
class UserMetadata extends CriterionHandler
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
        return $criterion instanceof Criterion\Matcher\UserMetadata;
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
        /** @var Criterion\Matcher\UserMetadata $criterion */
        switch ($criterion->target) {
            case Criterion\Matcher\UserMetadata::MODIFIER:
                return $query->expr->in(
                    $this->dbHandler->quoteColumn('creator_id', 'ezcontentobject_version'),
                    $criterion->value
                );

            case Criterion\Matcher\UserMetadata::GROUP:
                $subSelect = $query->subSelect();
                $subSelect
                    ->select(
                        $this->dbHandler->quoteColumn('contentobject_id', 't1')
                    )->from(
                        $query->alias(
                            $this->dbHandler->quoteTable('ezcontentobject_tree'),
                            't1'
                        )
                    )->innerJoin(
                        $query->alias(
                            $this->dbHandler->quoteTable('ezcontentobject_tree'),
                            't2'
                        ),
                        $query->expr->like(
                            't1.path_string',
                            $query->expr->concat(
                                't2.path_string',
                                $query->bindValue('%')
                            )
                        )
                    )->where(
                        $query->expr->in(
                            $this->dbHandler->quoteColumn('contentobject_id', 't2'),
                            $criterion->value
                        )
                    );

                return $query->expr->in(
                    $this->dbHandler->quoteColumn('owner_id', 'ezcontentobject'),
                    $subSelect
                );

            case Criterion\Matcher\UserMetadata::OWNER:
                return $query->expr->in(
                    $this->dbHandler->quoteColumn('owner_id', 'ezcontentobject'),
                    $criterion->value
                );
        }

        throw new RuntimeException("Invalid target criterion encountered:'" . $criterion->target . "'");
    }
}
