<?php

/**
 * File containing a DoctrineDatabase MapLocationDistance sort clause handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use PDO;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 */
class MapLocationDistance extends Field
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\MapLocationDistance;
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
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;
        $externalTable = $this->getSortTableName($number, 'ezgmaplocation');

        /*
         * Note: this formula is precise only for short distances.
         */
        $longitudeCorrectionByLatitude = cos(deg2rad($target->latitude)) ** 2;
        $distanceExpression = $query->expr->add(
            $query->expr->mul(
                $query->expr->sub(
                    $this->dbHandler->quoteColumn('latitude', $externalTable),
                    $query->bindValue($target->latitude)
                ),
                $query->expr->sub(
                    $this->dbHandler->quoteColumn('latitude', $externalTable),
                    $query->bindValue($target->latitude)
                )
            ),
            $query->expr->mul(
                $query->expr->sub(
                    $this->dbHandler->quoteColumn('longitude', $externalTable),
                    $query->bindValue($target->longitude)
                ),
                $query->expr->sub(
                    $this->dbHandler->quoteColumn('longitude', $externalTable),
                    $query->bindValue($target->longitude)
                ),
                $query->bindValue($longitudeCorrectionByLatitude)
            )
        );

        $query->select(
            $query->alias(
                $distanceExpression,
                $column1 = $this->getSortColumnName($number)
            )
        );

        return [$column1];
    }

    /**
     * Applies joins to the query, required to fetch sort data.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     * @param array $languageSettings
     */
    public function applyJoin(
        SelectQuery $query,
        SortClause $sortClause,
        $number,
        array $languageSettings
    ) {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $fieldTarget */
        $fieldTarget = $sortClause->targetData;
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        if (!isset($fieldMap[$fieldTarget->typeIdentifier][$fieldTarget->fieldIdentifier]['field_definition_id'])) {
            throw new InvalidArgumentException(
                '$sortClause->targetData',
                'No searchable fields found for the given sort clause target ' .
                "'{$fieldTarget->fieldIdentifier}' on '{$fieldTarget->typeIdentifier}'."
            );
        }

        $fieldDefinitionId = $fieldMap[$fieldTarget->typeIdentifier][$fieldTarget->fieldIdentifier]['field_definition_id'];
        $table = $this->getSortTableName($number);
        $externalTable = $this->getSortTableName($number, 'ezgmaplocation');

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
                    $this->getFieldCondition($query, $languageSettings, $table)
                )
            )
            ->leftJoin(
                $query->alias(
                    $this->dbHandler->quoteTable('ezgmaplocation'),
                    $this->dbHandler->quoteIdentifier($externalTable)
                ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentobject_version', $externalTable),
                        $this->dbHandler->quoteColumn('version', $table)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('contentobject_attribute_id', $externalTable),
                        $this->dbHandler->quoteColumn('id', $table)
                    )
                )
            );
    }
}
