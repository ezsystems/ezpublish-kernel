<?php

/**
 * File containing a DoctrineDatabase sort clause handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use PDO;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 */
class Field extends SortClauseHandler
{
    /**
     * Language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Content Type handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Creates a new Field sort clause handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        LanguageHandler $languageHandler,
        ContentTypeHandler $contentTypeHandler
    ) {
        $this->languageHandler = $languageHandler;
        $this->contentTypeHandler = $contentTypeHandler;

        parent::__construct($dbHandler);
    }

    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\Field;
    }

    /**
     * Apply selects to the query.
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
    public function applySelect(SelectQuery $query, SortClause $sortClause, $number)
    {
        $query
            ->select(
                $query->alias(
                    $query->expr->not(
                        $query->expr->isNull(
                            $this->dbHandler->quoteColumn(
                                'sort_key_int',
                                $this->getSortTableName($number)
                            )
                        )
                    ),
                    $column1 = $this->getSortColumnName($number . '_null')
                ),
                $query->alias(
                    $query->expr->not(
                        $query->expr->isNull(
                            $this->dbHandler->quoteColumn(
                                'sort_key_string',
                                $this->getSortTableName($number)
                            )
                        )
                    ),
                    $column2 = $this->getSortColumnName($number . '_bis_null')
                ),
                $query->alias(
                    $this->dbHandler->quoteColumn(
                        'sort_key_int',
                        $this->getSortTableName($number)
                    ),
                    $column3 = $this->getSortColumnName($number)
                ),
                $query->alias(
                    $this->dbHandler->quoteColumn(
                        'sort_key_string',
                        $this->getSortTableName($number)
                    ),
                    $column4 = $this->getSortColumnName($number . '_bis')
                )
            );

        return array($column1, $column2, $column3, $column4);
    }

    /**
     * Applies joins to the query, required to fetch sort data.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     */
    public function applyJoin(SelectQuery $query, SortClause $sortClause, $number)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $fieldTarget */
        $fieldTarget = $sortClause->targetData;
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        if (!isset($fieldMap[$fieldTarget->typeIdentifier][$fieldTarget->fieldIdentifier]['field_definition_id'])) {
            throw new InvalidArgumentException(
                "\$sortClause->targetData",
                'No searchable fields found for the given sort clause target ' .
                "'{$fieldTarget->fieldIdentifier}' on '{$fieldTarget->typeIdentifier}'."
            );
        }

        $fieldDefinitionId = $fieldMap[$fieldTarget->typeIdentifier][$fieldTarget->fieldIdentifier]['field_definition_id'];
        $table = $this->getSortTableName($number);

        if ($fieldTarget->languageCode === null) {
            $languageExpression = $query->expr->gt(
                $query->expr->bitAnd(
                    $query->expr->bitAnd($this->dbHandler->quoteColumn('language_id', $table), ~1),
                    $this->dbHandler->quoteColumn('initial_language_id', 'ezcontentobject')
                ),
                0
            );
        } else {
            $languageExpression = $query->expr->gt(
                $query->expr->bitAnd(
                    $query->expr->bitAnd($this->dbHandler->quoteColumn('language_id', $table), ~1),
                    $query->bindValue(
                        $this->languageHandler->loadByLanguageCode($fieldTarget->languageCode)->id,
                        null,
                        \PDO::PARAM_INT
                    )
                ),
                0
            );
        }

        $query
            ->leftJoin(
                $query->alias(
                    $this->dbHandler->quoteTable('ezcontentobject_attribute'),
                    $this->dbHandler->quoteIdentifier($table)
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $query->bindValue($fieldDefinitionId, null, PDO::PARAM_INT),
                        $this->dbHandler->quoteColumn('contentclassattribute_id', $table)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentobject_id', $table),
                        $this->dbHandler->quoteColumn('id', 'ezcontentobject')
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('version', $table),
                        $this->dbHandler->quoteColumn('current_version', 'ezcontentobject')
                    ),
                    $languageExpression
                )
            );
    }
}
