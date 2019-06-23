<?php

/**
 * File containing the DoctrineDatabase Collection field value handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
        $singleValueExpr = 'eq';

        switch ($criterion->operator) {
            case Criterion\Operator::LIKE:
                if (strpos($criterion->value, '%') !== false) {
                    // @deprecated In 6.13.x/7.3.x and higher, to be removed in 8.0
                    @trigger_error(
                        "Usage of '%' in Operator::LIKE criteria with Legacy Search Engine was never intended, " .
                        "and is deprecated for removal in 8.0. Please use '*' like in FullText, works across engines",
                        E_USER_DEPRECATED
                    );
                    $value = $this->lowerCase($criterion->value);
                } else {
                    // Allow usage of * as wildcards in ::LIKE
                    $value = str_replace('*', '%', $this->prepareLikeString($criterion->value));
                }
                $singleValueExpr = 'like';
                // No break here, rest is handled by shared code with ::CONTAINS below

            case Criterion\Operator::CONTAINS:
                $value = isset($value) ? $value : $this->prepareLikeString($criterion->value);
                $quotedColumn = $this->dbHandler->quoteColumn($column);
                $filter = $query->expr->lOr(
                    [
                        $query->expr->$singleValueExpr(
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
                    ]
                );
                break;

            default:
                $filter = parent::handle($query, $criterion, $column);
        }

        return $filter;
    }
}
