<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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

    public function __construct(
        Connection $connection,
        LanguageHandler $languageHandler,
        ContentTypeHandler $contentTypeHandler
    ) {
        parent::__construct($connection);

        $this->languageHandler = $languageHandler;
        $this->contentTypeHandler = $contentTypeHandler;
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
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param int $number
     *
     * @return array
     */
    public function applySelect(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number
    ): array {
        $query
            ->addSelect(
                sprintf(
                    '%s AS %s',
                    $query->expr()->isNotNull(
                        $this->getSortTableName($number) . '.sort_key_int'
                    ),
                    $column1 = $this->getSortColumnName($number . '_null')
                ),
                sprintf(
                    '%s AS %s',
                    $query->expr()->isNotNull(
                        $this->getSortTableName($number) . '.sort_key_string'
                    ),
                    $column2 = $this->getSortColumnName($number . '_bis_null')
                ),
                sprintf(
                    '%s AS %s',
                    $this->getSortTableName($number) . '.sort_key_int',
                    $column3 = $this->getSortColumnName($number)
                ),
                sprintf(
                    '%s AS %s',
                    $this->getSortTableName($number) . '.sort_key_string',
                    $column4 = $this->getSortColumnName($number . '_bis')
                )
            );

        return [$column1, $column2, $column3, $column4];
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

        $tableAlias = $this->connection->quoteIdentifier($table);
        $query
            ->leftJoin(
                'c',
                Gateway::CONTENT_FIELD_TABLE,
                $tableAlias,
                $query->expr()->andX(
                    $query->expr()->eq(
                        $query->createNamedParameter(
                            $fieldDefinitionId,
                            ParameterType::INTEGER
                        ),
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
                    $this->getFieldCondition($query, $languageSettings, $table)
                )
            );
    }

    protected function getFieldCondition(
        QueryBuilder $query,
        array $languageSettings,
        $fieldTableName
    ) {
        // 1. Use main language(s) by default
        if (empty($languageSettings['languages'])) {
            return $query->expr()->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.initial_language_id',
                    $fieldTableName . '.language_id'
                ),
                $query->createNamedParameter(0, ParameterType::INTEGER)
            );
        }

        // 2. Otherwise use prioritized languages
        $leftSide = $this->dbPlatform->getBitAndComparisonExpression(
            sprintf(
                'c.language_mask - %s',
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    $fieldTableName . '.language_id'
                )
            ),
            $query->createNamedParameter(1, ParameterType::INTEGER)
        );
        $rightSide = $this->dbPlatform->getBitAndComparisonExpression(
            $fieldTableName . '.language_id',
            $query->createNamedParameter(1, ParameterType::INTEGER)
        );

        for ($index = count(
                $languageSettings['languages']
            ) - 1, $multiplier = 2; $index >= 0; $index--, $multiplier *= 2) {
            $languageId = $this->languageHandler
                ->loadByLanguageCode($languageSettings['languages'][$index])->id;

            $addToLeftSide = $this->dbPlatform->getBitAndComparisonExpression(
                sprintf(
                    'c.language_mask - %s',
                    $this->dbPlatform->getBitAndComparisonExpression(
                        'c.language_mask',
                        $fieldTableName . '.language_id'
                    )
                ),
                $query->createNamedParameter($languageId, ParameterType::INTEGER)
            );
            $addToRightSide = $this->dbPlatform->getBitAndComparisonExpression(
                $fieldTableName . '.language_id',
                $query->createNamedParameter($languageId, ParameterType::INTEGER)
            );

            if ($multiplier > $languageId) {
                $factor = $multiplier / $languageId;
                for ($shift = 0; $factor > 1; $factor = $factor / 2, $shift++);
                $factorTerm = ' << ' . $shift;
                $addToLeftSide .= $factorTerm;
                $addToRightSide .= $factorTerm;
            } elseif ($multiplier < $languageId) {
                $factor = $languageId / $multiplier;
                for ($shift = 0; $factor > 1; $factor = $factor / 2, $shift++);
                $factorTerm = ' >> ' . $shift;
                $addToLeftSide .= $factorTerm;
                $addToRightSide .= $factorTerm;
            }

            $leftSide = "$leftSide + ($addToLeftSide)";
            $rightSide = "$rightSide + ($addToRightSide)";
        }

        return $query->expr()->andX(
            $query->expr()->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    $fieldTableName . '.language_id'
                ),
                $query->createNamedParameter(0, ParameterType::INTEGER)
            ),
            $query->expr()->lt($leftSide, $rightSide)
        );
    }
}
