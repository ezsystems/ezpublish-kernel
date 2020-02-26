<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

class UserLogin extends CriterionHandler
{
    /** @var \eZ\Publish\Core\Persistence\TransformationProcessor */
    private $transformationProcessor;

    public function __construct(DatabaseHandler $dbHandler, TransformationProcessor $transformationProcessor)
    {
        parent::__construct($dbHandler);

        $this->transformationProcessor = $transformationProcessor;
    }

    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\UserLogin;
    }

    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        if (Criterion\Operator::LIKE === $criterion->operator) {
            $expression = $query->expr->like(
                $this->dbHandler->quoteColumn('login', 't1'),
                $query->bindValue(
                    str_replace(
                        '*',
                        '%',
                        addcslashes(
                            $this->transformationProcessor->transformByGroup(
                                $criterion->value,
                                'lowercase'
                            ),
                            '%_'
                        )
                    )
                )
            );
        } else {
            $expression = $query->expr->in(
                $this->dbHandler->quoteColumn('login', 't1'),
                $criterion->value
            );
        }

        $subSelect = $query->subSelect();
        $subSelect
            ->select(
                $this->dbHandler->quoteColumn('contentobject_id', 't1')
            )->from(
                $query->alias(
                    $this->dbHandler->quoteTable('ezuser'),
                    't1'
                )
            )->where(
                $expression
            );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
