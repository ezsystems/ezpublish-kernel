<?php
/**
 * File containing the EzcDatabase Collection field value handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FieldValue\Handler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
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
     * Creates a new criterion handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     * @param string $separator
     */
    public function __construct( EzcDbHandler $dbHandler, TransformationProcessor $transformationProcessor, $separator )
    {
        $this->dbHandler = $dbHandler;
        $this->transformationProcessor = $transformationProcessor;
        $this->separator = $separator;
    }

    /**
     * Generates query expression for operator and value of a Field Criterion.
     *
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \ezcQueryExpression
     */
    public function handle( ezcQuerySelect $query, Criterion $criterion, $column )
    {
        switch ( $criterion->operator )
        {
            case Criterion\Operator::CONTAINS:
                $quotedColumn = $this->dbHandler->quoteColumn( $column );
                $value = $this->lowerCase( $criterion->value );
                $filter = $query->expr->lOr(
                    array(
                        $query->expr->eq(
                            $quotedColumn,
                            $query->bindValue( $value, null, \PDO::PARAM_STR )
                        ),
                        $query->expr->like(
                            $quotedColumn,
                            $query->bindValue(
                                "%" . $this->separator . $value,
                                null,
                                \PDO::PARAM_STR
                            )
                        ),
                        $query->expr->like(
                            $quotedColumn,
                            $query->bindValue(
                                $value . $this->separator . "%",
                                null,
                                \PDO::PARAM_STR
                            )
                        ),
                        $query->expr->like(
                            $quotedColumn,
                            $query->bindValue(
                                "%" . $this->separator . $value . $this->separator . "%",
                                null,
                                \PDO::PARAM_STR
                            )
                        )
                    )
                );
                break;

            default:
                $filter = parent::handle( $query, $criterion, $column );
        }

        return $filter;
    }
}
