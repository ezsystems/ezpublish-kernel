<?php
/**
 * File containing a EzcDatabase sort clause handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\SortClauseHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
 */
class Field extends SortClauseHandler
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function accept( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\Field;
    }

    /**
     * Apply selects to the query
     *
     * Returns the name of the (aliased) column, which information should be
     * used for sorting.
     *
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return string
     */
    public function applySelect( ezcQuerySelect $query, SortClause $sortClause, $number )
    {
        $query
            ->select(
                $query->alias(
                    $this->dbHandler->quoteColumn(
                        "sort_key_int",
                        $this->getSortTableName( $number )
                    ),
                    $column1 = $this->getSortColumnName( $number )
                ),
                $query->alias(
                    $this->dbHandler->quoteColumn(
                        "sort_key_string",
                        $this->getSortTableName( $number )
                    ),
                    $column2 = $this->getSortColumnName( $number . "_bis" )
                )
            );

        return array( $column1, $column2 );
    }

    /**
     * Applies joins to the query, required to fetch sort data
     *
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return void
     */
    public function applyJoin( ezcQuerySelect $query, SortClause $sortClause, $number )
    {
        $table = $this->getSortTableName( $number );
        $query
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "ezcontentobject_attribute" ),
                    $this->dbHandler->quoteIdentifier( $table )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "contentobject_id", $table ),
                        $this->dbHandler->quoteColumn( "id", "ezcontentobject" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "version", $table ),
                        $this->dbHandler->quoteColumn( "current_version", "ezcontentobject" )
                    )
                )
            )
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "ezcontentclass_attribute" ),
                    $this->dbHandler->quoteIdentifier( "cc_attr_$number" )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "contentclassattribute_id", $table ),
                        $this->dbHandler->quoteColumn( "id", "cc_attr_$number" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "identifier", "cc_attr_$number" ),
                        $query->bindValue( $sortClause->targetData->fieldIdentifier )
                    )
                )
            )
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "ezcontentclass" ),
                    $this->dbHandler->quoteIdentifier( "cc_$number" )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "contentclass_id", "cc_attr_$number" ),
                        $this->dbHandler->quoteColumn( "id", "cc_$number" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "identifier", "cc_$number" ),
                        $query->bindValue( $sortClause->targetData->typeIdentifier )
                    )
                )
            );
    }
}
