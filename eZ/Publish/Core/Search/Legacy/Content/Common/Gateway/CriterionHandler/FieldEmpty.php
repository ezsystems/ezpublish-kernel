<?php

/**
 * File containing the DoctrineDatabase field criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

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

    /**
     * Construct from handler handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $fieldConverterRegistry
     */
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
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\IsFieldEmpty;
    }

    /**
     * Returns relevant field information for the specified field.
     *
     * The returned information is returned as an array of the attribute
     * identifier and the sort column, which should be used.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     * @throws \RuntimeException if no converter is found
     *
     * @param string $fieldIdentifier
     *
     * @return array
     *
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     */
    protected function getFieldsInformation($fieldIdentifier)
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
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
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

            if ($criterion->value[0] == Criterion\IsFieldEmpty::EMPTY) {
                $filter = sprintf('`%s` = :%s', $fieldsInfo['column'], $fieldTypeIdentifier);
            }
            if ($criterion->value[0] == Criterion\IsFieldEmpty::NOT_EMPTY) {
                $filter = sprintf('`%s` != :%s', $fieldsInfo['column'], $fieldTypeIdentifier);
            }

            $subSelect->bindParam($fieldsInfo['empty_value'], $fieldTypeIdentifier);

            $whereExpressions[] = $subSelect->expr->lAnd(
                $subSelect->expr->in(
                    $this->dbHandler->quoteColumn('contentclassattribute_id'),
                    $fieldsInfo['ids']
                ),
                $filter
            );
        }

        if (empty($whereExpressions)) {
            throw new NotImplementedException(
                sprintf(
                    'Following fieldtypes are not searchable in the legacy search engine: %s',
                    implode(', ', array_keys($fieldsInformation))
                )
            );
        }

        $subSelect->where(
            $subSelect->expr->lAnd(
                $subSelect->expr->eq(
                    $this->dbHandler->quoteColumn('version', 'ezcontentobject_attribute'),
                    $this->dbHandler->quoteColumn('current_version', 'ezcontentobject')
                ),
                count($whereExpressions) > 1 ? $subSelect->expr->lOr($whereExpressions) : $whereExpressions[0],
                $this->getFieldCondition($subSelect, $languageSettings)
            )
        );

        return $query->expr->in(
            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
            $subSelect
        );
    }
}
