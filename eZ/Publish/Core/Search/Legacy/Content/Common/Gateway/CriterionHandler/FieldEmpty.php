<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\Database\Expression;

/**
 * Field criterion handler.
 */
class FieldEmpty extends FieldBase
{
    /**
     * Field converter registry.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $fieldConverterRegistry;

    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    public function __construct(
        DatabaseHandler $dbHandler,
        ContentTypeHandler $contentTypeHandler,
        LanguageHandler $languageHandler,
        Registry $fieldConverterRegistry,
        FieldTypeService $fieldTypeService
    ) {
        parent::__construct($dbHandler, $contentTypeHandler, $languageHandler);

        $this->fieldConverterRegistry = $fieldConverterRegistry;
        $this->fieldTypeService = $fieldTypeService;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     */
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\IsFieldEmpty;
    }

    /**
     * Returns relevant field information for the specified field.
     *
     * Returns an array of the attribute,
     * identifier and the sort column, which should be used.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     * @throws \RuntimeException if no converter is found
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function getFieldsInformation(string $fieldIdentifier): array
    {
        $fieldMapArray = [];
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        foreach ($fieldMap as $contentTypeIdentifier => $fieldIdentifierMap) {
            if (!isset($fieldIdentifierMap[$fieldIdentifier])) {
                continue;
            }

            $fieldTypeIdentifier = $fieldIdentifierMap[$fieldIdentifier]['field_type_identifier'];
            $fieldMapArray[$fieldTypeIdentifier]['ids'][] = $fieldIdentifierMap[$fieldIdentifier]['field_definition_id'];
            if (!isset($fieldMapArray[$fieldTypeIdentifier]['column'])) {
                $fieldMapArray[$fieldTypeIdentifier]['column'] = $this->fieldConverterRegistry->getConverter($fieldTypeIdentifier)->getIndexColumn();
            }

            $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);
            $fieldMapArray[$fieldTypeIdentifier]['empty_value'] = $fieldType->getEmptyValue();
        }

        if (empty($fieldMapArray)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$fieldIdentifier}'."
            );
        }

        return $fieldMapArray;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException If no searchable fields are found for the given criterion target.
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ): string {
        $fieldsInformation = $this->getFieldsInformation($criterion->target);

        $subSelect = $query->subSelect();
        $subSelect->select(
            $this->dbHandler->quoteColumn('contentobject_id')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_attribute')
        );

        $whereExpressions = [];

        foreach ($fieldsInformation as $fieldTypeIdentifier => $fieldsInfo) {
            if ($fieldsInfo['column'] === false) {
                continue;
            }

            $filterPlaceholder = $subSelect->bindValue(
                $fieldsInfo['empty_value'],
                ':fieldTypeIdentifier'
            );
            $filter = $criterion->value[0]
                ? $subSelect->expr->eq($fieldsInfo['column'], $filterPlaceholder)
                : $subSelect->expr->neq($fieldsInfo['column'], $filterPlaceholder);

            $whereExpressions[] = $subSelect->expr->lAnd(
                $subSelect->expr->in(
                    $this->dbHandler->quoteColumn('contentclassattribute_id'),
                    $fieldsInfo['ids']
                ),
                $filter
            );
        }

        return $this->getInExpressionWithFieldConditions(
            $query,
            $subSelect,
            $languageSettings,
            $whereExpressions,
            $fieldsInformation
        );
    }
}
