<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Query\CriteriaConverter;
use RuntimeException;

class UserMetadata extends Base
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\SectionId;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, QueryBuilder $query, Criterion $criterion): string
    {
        $expr = $query->expr();
        /*return $expr->in(
            'content.section_id',
            $query->createNamedParameter((array)$criterion->value, Connection::PARAM_INT_ARRAY)
        );*/

        /* @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata $criterion */
        switch ($criterion->target) {

            case Criterion\UserMetadata::MODIFIER:
                $this->addInnerJoinIfNeeded(
                    $query,
                    'content',
                    'ezcontentobject_version',
                    'version',
                    'version.contentobject_id = content.id AND version.version = content.current_version'
                );

                return $this->operateInt($query, 'version.creator_id', $criterion);


            // @TODO Needed for permissions
            /*case Criterion\UserMetadata::GROUP:
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
                );*/

            case Criterion\UserMetadata::OWNER:
                return $this->operateInt($query, 'content.owner_id', $criterion);
        }

        throw new RuntimeException("Invalid target criterion encountered:'" . $criterion->target . "'");
    }
}
