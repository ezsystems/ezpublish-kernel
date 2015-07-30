<?php

/**
 * File containing the DoctrineDatabase Collection field value handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 *
 * Collection value handler is used for creating a filter on a value that is in fact a collection of values,
 * separated by a character.
 * Eg. list of countries, list of Selection options, list of RelationList Content ids
 */
class Collection extends Handler
{
    /**
     * Character separating indexed values.
     *
     * @var string
     */
    protected $separator;

    /**
     * Creates a new criterion handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     * @param string $separator
     */
    public function __construct(DatabaseHandler $dbHandler, TransformationProcessor $transformationProcessor, $separator)
    {
        $this->dbHandler = $dbHandler;
        $this->transformationProcessor = $transformationProcessor;
        $this->separator = $separator;
    }

    /**
     * Generates query expression for operator and value of a Field Criterion.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(SelectQuery $query, Criterion $criterion, $column)
    {
        switch ($criterion->operator) {
            case Criterion\Operator::CONTAINS:
                $quotedColumn = $this->dbHandler->quoteColumn($column);
                $value = $this->lowerCase($criterion->value);
                $filter = $query->expr->lOr(
                    array(
                        $query->expr->eq(
                            $quotedColumn,
                            $query->bindValue($value, null, \PDO::PARAM_STR)
                        ),
                        $query->expr->like(
                            $quotedColumn,
                            $query->bindValue(
                                '%' . $this->separator . $value,
                                null,
                                \PDO::PARAM_STR
                            )
                        ),
                        $query->expr->like(
                            $quotedColumn,
                            $query->bindValue(
                                $value . $this->separator . '%',
                                null,
                                \PDO::PARAM_STR
                            )
                        ),
                        $query->expr->like(
                            $quotedColumn,
                            $query->bindValue(
                                '%' . $this->separator . $value . $this->separator . '%',
                                null,
                                \PDO::PARAM_STR
                            )
                        ),
                    )
                );
                break;

            default:
                $filter = parent::handle($query, $criterion, $column);
        }

        return $filter;
    }
}
