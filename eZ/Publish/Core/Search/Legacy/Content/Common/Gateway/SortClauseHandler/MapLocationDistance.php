<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;

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

    public function applySelect(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number
    ): array {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;
        $externalTable = $this->getSortTableName($number, 'ezgmaplocation');

        // note: avoid using literal names for parameters to account for multiple visits of the same Criterion
        $latitudePlaceholder = $query->createNamedParameter($target->latitude);
        $longitudePlaceholder = $query->createNamedParameter($target->longitude);

        // note: can have literal name for all visits of this Criterion because it's constant
        $query->setParameter(':longitude_correction', cos(deg2rad($target->latitude)) ** 2);

        // build: (latitude1 - latitude2)^2 + (longitude2 - longitude2)^2 * longitude_correction)
        $latitudeSubstrExpr = "({$externalTable}.latitude - {$latitudePlaceholder})";
        $longitudeSubstrExpr = "({$externalTable}.longitude - {$longitudePlaceholder})";
        $latitudeExpr = "{$latitudeSubstrExpr} * {$latitudeSubstrExpr}";
        $longitudeExpr = "{$longitudeSubstrExpr} * {$longitudeSubstrExpr} * :longitude_correction";
        $distanceExpression = "{$latitudeExpr} + {$longitudeExpr}";

        $query->addSelect(
            sprintf('%s AS %s', $distanceExpression, $column1 = $this->getSortColumnName($number))
        );

        return [$column1];
    }

    public function applyJoin(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number,
        array $languageSettings
    ): void {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget $fieldTarget */
        $fieldTarget = $sortClause->targetData;
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        if (!isset($fieldMap[$fieldTarget->typeIdentifier][$fieldTarget->fieldIdentifier]['field_definition_id'])) {
            throw new InvalidArgumentException(
                '$sortClause->targetData',
                'No searchable Fields found for the provided Sort Clause target ' .
                "'{$fieldTarget->fieldIdentifier}' on '{$fieldTarget->typeIdentifier}'."
            );
        }

        $fieldDefinitionId = $fieldMap[$fieldTarget->typeIdentifier][$fieldTarget->fieldIdentifier]['field_definition_id'];
        $table = $this->getSortTableName($number);
        $externalTable = $this->getSortTableName($number, 'ezgmaplocation');

        $tableAlias = $this->connection->quoteIdentifier($table);
        $externalTableAlias = $this->connection->quoteIdentifier($externalTable);
        $query
            ->leftJoin(
                'c',
                ContentGateway::CONTENT_FIELD_TABLE,
                $tableAlias,
                $query->expr()->andX(
                    $query->expr()->eq(
                        $query->createNamedParameter($fieldDefinitionId, ParameterType::INTEGER),
                        $tableAlias . '.contentclassattribute_id'
                    ),
                    $query->expr()->eq(
                        $tableAlias . '.contentobject_id',
                        'c.id'
                    ),
                    $query->expr()->eq(
                        $tableAlias . '.version',
                        'c.current_version'
                    ),
                    $this->getFieldCondition($query, $languageSettings, $tableAlias)
                )
            )
            ->leftJoin(
                $tableAlias,
                'ezgmaplocation',
                $externalTableAlias,
                $query->expr()->andX(
                    $query->expr()->eq(
                        $externalTableAlias . '.contentobject_version',
                        $tableAlias . '.version'
                    ),
                    $query->expr()->eq(
                        $externalTableAlias . '.contentobject_attribute_id',
                        $tableAlias . '.id'
                    )
                )
            );
    }
}
