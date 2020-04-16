<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Converter as FieldValueConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

/**
 * Field criterion handler.
 */
class Field extends FieldBase
{
    /**
     * Field converter registry.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $fieldConverterRegistry;

    /**
     * Field value converter.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Converter
     */
    protected $fieldValueConverter;

    /**
     * Transformation processor.
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    public function __construct(
        Connection $connection,
        ContentTypeHandler $contentTypeHandler,
        LanguageHandler $languageHandler,
        Registry $fieldConverterRegistry,
        FieldValueConverter $fieldValueConverter,
        TransformationProcessor $transformationProcessor
    ) {
        parent::__construct($connection, $contentTypeHandler, $languageHandler);

        $this->fieldConverterRegistry = $fieldConverterRegistry;
        $this->fieldValueConverter = $fieldValueConverter;
        $this->transformationProcessor = $transformationProcessor;
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
        return $criterion instanceof Criterion\Field;
    }

    /**
     * Returns relevant field information for the specified field.
     *
     * The returned information is returned as an array of the attribute
     * identifier and the sort column, which should be used.
     *
     * @param string $fieldIdentifier
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     * @throws \RuntimeException if no converter is found
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     */
    protected function getFieldsInformation($fieldIdentifier)
    {
        $fieldMapArray = [];
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        foreach ($fieldMap as $fieldIdentifierMap) {
            // First check if field exists in the current ContentType, there is nothing to do if it doesn't
            if (!isset($fieldIdentifierMap[$fieldIdentifier])) {
                continue;
            }

            $fieldTypeIdentifier = $fieldIdentifierMap[$fieldIdentifier]['field_type_identifier'];
            $fieldMapArray[$fieldTypeIdentifier]['ids'][] = $fieldIdentifierMap[$fieldIdentifier]['field_definition_id'];
            if (!isset($fieldMapArray[$fieldTypeIdentifier]['column'])) {
                $fieldMapArray[$fieldTypeIdentifier]['column'] = $this->fieldConverterRegistry->getConverter(
                    $fieldTypeIdentifier
                )->getIndexColumn();
            }
        }

        if (empty($fieldMapArray)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable Fields found for the provided Criterion target '{$fieldIdentifier}'."
            );
        }

        return $fieldMapArray;
    }

    /**
     * @param array $languageSettings
     *
     * @return string
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException If no searchable fields are found for the given criterion target.
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $fieldsInformation = $this->getFieldsInformation($criterion->target);

        $subSelect = $this->connection->createQueryBuilder();
        $subSelect
            ->select('contentobject_id')
            ->from(ContentGateway::CONTENT_FIELD_TABLE, 'f_def');

        $whereExpressions = [];

        foreach ($fieldsInformation as $fieldTypeIdentifier => $fieldsInfo) {
            if ($fieldsInfo['column'] === false) {
                continue;
            }

            $filter = $this->fieldValueConverter->convertCriteria(
                $fieldTypeIdentifier,
                $queryBuilder,
                $subSelect,
                $criterion,
                $fieldsInfo['column']
            );

            $whereExpressions[] = $subSelect->expr()->andX(
                $subSelect->expr()->in(
                    'contentclassattribute_id',
                    $queryBuilder->createNamedParameter(
                        $fieldsInfo['ids'],
                        Connection::PARAM_INT_ARRAY
                    )
                ),
                $filter
            );
        }

        return $this->getInExpressionWithFieldConditions(
            $queryBuilder,
            $subSelect,
            $languageSettings,
            $whereExpressions,
            $fieldsInformation
        );
    }
}
