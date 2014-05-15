<?php
/**
 * File containing a DoctrineDatabase sort clause handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseHandler;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 */
class Field extends SortClauseHandler
{
    /**
     * Language handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Creates a new Field sort clause handler
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct( DatabaseHandler $dbHandler, LanguageHandler $languageHandler )
    {
        $this->languageHandler = $languageHandler;
        parent::__construct( $dbHandler );
    }

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
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return string
     */
    public function applySelect( SelectQuery $query, SortClause $sortClause, $number )
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
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return void
     */
    public function applyJoin( SelectQuery $query, SortClause $sortClause, $number )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $fieldTarget */
        $fieldTarget = $sortClause->targetData;
        $table = $this->getSortTableName( $number );

        if ( $fieldTarget->languageCode === null )
        {
            $linkTable = $table;
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
                        ),
                        $query->expr->gt(
                            $query->expr->bitAnd(
                                $query->expr->bitAnd( $this->dbHandler->quoteColumn( "language_id", $table ), ~1 ),
                                $this->dbHandler->quoteColumn( "initial_language_id", "ezcontentobject" )
                            ),
                            0
                        )
                    )
                );
        }
        else
        {
            $linkTable = $table . "_main_language";
            $query
                ->innerJoin(
                    $query->alias(
                        $this->dbHandler->quoteTable( "ezcontentobject_attribute" ),
                        $this->dbHandler->quoteIdentifier( $linkTable )
                    ),
                    $query->expr->lAnd(
                        $query->expr->eq(
                            $this->dbHandler->quoteColumn( "contentobject_id", $linkTable ),
                            $this->dbHandler->quoteColumn( "id", "ezcontentobject" )
                        ),
                        $query->expr->eq(
                            $this->dbHandler->quoteColumn( "version", $linkTable ),
                            $this->dbHandler->quoteColumn( "current_version", "ezcontentobject" )
                        ),
                        $query->expr->gt(
                            $query->expr->bitAnd(
                                $query->expr->bitAnd( $this->dbHandler->quoteColumn( "language_id", $linkTable ), ~1 ),
                                $this->dbHandler->quoteColumn( "initial_language_id", "ezcontentobject" )
                            ),
                            0
                        )
                    )
                )
                ->leftJoin(
                    $query->alias(
                        $this->dbHandler->quoteTable( "ezcontentobject_attribute" ),
                        $this->dbHandler->quoteIdentifier( $table )
                    ),
                    $query->expr->lAnd(
                        $query->expr->eq(
                            $this->dbHandler->quoteColumn( "contentobject_id", $linkTable ),
                            $this->dbHandler->quoteColumn( "contentobject_id", $table )
                        ),
                        $query->expr->eq(
                            $this->dbHandler->quoteColumn( "contentclassattribute_id", $linkTable ),
                            $this->dbHandler->quoteColumn( "contentclassattribute_id", $table )
                        ),
                        $query->expr->eq(
                            $this->dbHandler->quoteColumn( "version", $linkTable ),
                            $this->dbHandler->quoteColumn( "version", $table )
                        ),
                        $query->expr->gt(
                            $query->expr->bitAnd(
                                $query->expr->bitAnd( $this->dbHandler->quoteColumn( "language_id", $table ), ~1 ),
                                $query->bindValue(
                                    $this->languageHandler->loadByLanguageCode( $fieldTarget->languageCode )->id,
                                    null,
                                    \PDO::PARAM_INT
                                )
                            ),
                            0
                        )
                    )
                );
        }

        $query
            ->innerJoin(
                $query->alias(
                    $this->dbHandler->quoteTable( "ezcontentclass_attribute" ),
                    $this->dbHandler->quoteIdentifier( "cc_attr_$number" )
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "contentclassattribute_id", $linkTable ),
                        $this->dbHandler->quoteColumn( "id", "cc_attr_$number" )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "identifier", "cc_attr_$number" ),
                        $query->bindValue( $fieldTarget->fieldIdentifier )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "version", "cc_attr_$number" ),
                        $query->bindValue( Type::STATUS_DEFINED, null, \PDO::PARAM_INT )
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
                        $query->bindValue( $fieldTarget->typeIdentifier )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( "version", "cc_$number" ),
                        $query->bindValue( Type::STATUS_DEFINED, null, \PDO::PARAM_INT )
                    )
                )
            );
    }
}
