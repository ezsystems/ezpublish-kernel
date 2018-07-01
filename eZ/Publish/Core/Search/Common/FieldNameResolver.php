<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * Provides search backend field name resolving for criteria and sort clauses
 * targeting Content fields.
 */
class FieldNameResolver
{
    /**
     * Field registry.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * Content type handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Field name generator.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameGenerator
     */
    protected $nameGenerator;

    /**
     * Create from search field registry, content type handler and field name generator.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Search\Common\FieldNameGenerator $nameGenerator
     */
    public function __construct(
        FieldRegistry $fieldRegistry,
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $nameGenerator
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Get content type, field definition and field type mapping information.
     *
     * Returns an array in the form:
     *
     * <code>
     *  array(
     *      "<ContentType identifier>" => array(
     *          "<FieldDefinition identifier>" => array(
     *              "field_definition_id" => "<FieldDefinition id>",
     *              "field_type_identifier" => "<FieldType identifier>",
     *          ),
     *          ...
     *      ),
     *      ...
     *  )
     * </code>
     *
     * @return array[]
     */
    protected function getSearchableFieldMap()
    {
        return $this->contentTypeHandler->getSearchableFieldMap();
    }

    /**
     * For the given parameters returns a set of search backend field names to search on.
     *
     * The method will check for custom fields if given $criterion implements
     * CustomFieldInterface. With optional parameters $fieldTypeIdentifier and
     * $name specific field type and field from its Indexable implementation
     * can be targeted.
     *
     * @deprecated since 6.2, use getFieldTypes instead
     *
     * @see \eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface
     * @see \eZ\Publish\SPI\FieldType\Indexable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $fieldDefinitionIdentifier
     * @param null|string $fieldTypeIdentifier
     * @param null|string $name
     *
     * @return string[]
     */
    public function getFieldNames(
        Criterion $criterion,
        $fieldDefinitionIdentifier,
        $fieldTypeIdentifier = null,
        $name = null
    ) {
        $fieldTypeNameMap = $this->getFieldTypes($criterion, $fieldDefinitionIdentifier, $fieldTypeIdentifier, $name);

        return array_keys($fieldTypeNameMap);
    }

    /**
     * For the given parameters returns a set of search backend field names/types to search on.
     *
     * The method will check for custom fields if given $criterion implements
     * CustomFieldInterface. With optional parameters $fieldTypeIdentifier and
     * $name specific field type and field from its Indexable implementation
     * can be targeted.
     *
     * @see \eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface
     * @see \eZ\Publish\SPI\FieldType\Indexable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $fieldDefinitionIdentifier
     * @param null|string $fieldTypeIdentifier
     * @param null|string $name
     *
     * @return array<string, \eZ\Publish\SPI\Search\FieldType>
     */
    public function getFieldTypes(
        Criterion $criterion,
        $fieldDefinitionIdentifier,
        $fieldTypeIdentifier = null,
        $name = null
    ) {
        $fieldMap = $this->getSearchableFieldMap();
        $fieldTypeNameMap = [];

        foreach ($fieldMap as $contentTypeIdentifier => $fieldIdentifierMap) {
            // First check if field exists in the current ContentType, there is nothing to do if it doesn't
            if (!isset($fieldIdentifierMap[$fieldDefinitionIdentifier])) {
                continue;
            }

            // If $fieldTypeIdentifier is given it must match current field definition
            if (
                $fieldTypeIdentifier !== null &&
                $fieldTypeIdentifier !== $fieldIdentifierMap[$fieldDefinitionIdentifier]['field_type_identifier']
            ) {
                continue;
            }

            $fieldNameWithSearchType = $this->getIndexFieldName(
                $criterion,
                $contentTypeIdentifier,
                $fieldDefinitionIdentifier,
                $fieldIdentifierMap[$fieldDefinitionIdentifier]['field_type_identifier'],
                $name,
                false
            );

            $fieldNames = array_keys($fieldNameWithSearchType);
            $fieldName = reset($fieldNames);

            $fieldTypeNameMap[$fieldName] = $fieldNameWithSearchType[$fieldName];
        }

        return $fieldTypeNameMap;
    }

    /**
     * For the given parameters returns search backend field name to sort on or
     * null if the field could not be found.
     *
     * The method will check for custom fields if given $sortClause implements
     * CustomFieldInterface. With optional parameter $name specific field from
     * field type's Indexable implementation can be targeted.
     *
     * Will return null if no sortable field is found.
     *
     * @see \eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface
     * @see \eZ\Publish\SPI\FieldType\Indexable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param null|string $name
     *
     * @return null|string
     */
    public function getSortFieldName(
        SortClause $sortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $name = null
    ) {
        $fieldMap = $this->getSearchableFieldMap();

        // First check if field exists in type, there is nothing to do if it doesn't
        if (!isset($fieldMap[$contentTypeIdentifier][$fieldDefinitionIdentifier])) {
            return null;
        }

        $fieldName = array_keys($this->getIndexFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier,
            $fieldMap[$contentTypeIdentifier][$fieldDefinitionIdentifier]['field_type_identifier'],
            $name,
            true
        ));

        return reset($fieldName);
    }

    /**
     * Returns index field name for the given parameters.
     *
     * @param object $criterionOrSortClause
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param string $fieldTypeIdentifier
     * @param string $name
     * @param bool $isSortField
     *
     * @return string
     */
    public function getIndexFieldName(
        $criterionOrSortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $fieldTypeIdentifier,
        $name,
        $isSortField
    ) {
        // If criterion or sort clause implements CustomFieldInterface and custom field is set for
        // ContentType/FieldDefinition, return it
        if (
            $criterionOrSortClause instanceof CustomFieldInterface &&
            $customFieldName = $criterionOrSortClause->getCustomField(
                $contentTypeIdentifier,
                $fieldDefinitionIdentifier
            )
        ) {
            return [$customFieldName => null];
        }

        // Else, generate field name from field type's index definition

        $indexFieldType = $this->fieldRegistry->getType($fieldTypeIdentifier);

        // If $name is not given use default field name
        if ($name === null) {
            if ($isSortField) {
                $name = $indexFieldType->getDefaultSortField();
            } else {
                $name = $indexFieldType->getDefaultMatchField();
            }
        }

        $indexDefinition = $indexFieldType->getIndexDefinition();

        // Should only happen by mistake, so let's throw if it does
        if (!isset($indexDefinition[$name])) {
            throw new RuntimeException(
                "Could not find '{$name}' field in '{$fieldTypeIdentifier}' field type's index definition"
            );
        }

        $field = $this->nameGenerator->getTypedName(
            $this->nameGenerator->getName(
                $name,
                $fieldDefinitionIdentifier,
                $contentTypeIdentifier
            ),
            $indexDefinition[$name]
        );

        return [$field => $indexDefinition[$name]];
    }
}
