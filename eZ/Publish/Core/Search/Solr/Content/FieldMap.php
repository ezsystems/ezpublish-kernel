<?php
/**
 * File containing the field map class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content;

use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * Provides field mapping information
 */
class FieldMap
{
    /**
     * Field registry
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * Content type handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Field name generator
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldNameGenerator
     */
    protected $nameGenerator;

    /**
     * Available field types
     *
     * @var array
     */
    protected $fieldTypes;

    /**
     * Create from content type handler and field registry
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Search\Solr\Content\FieldNameGenerator $nameGenerator
     */
    public function __construct(
        FieldRegistry $fieldRegistry,
        ContentTypeHandler $contentTypeHandler,
        FieldNameGenerator $nameGenerator
    )
    {
        $this->fieldRegistry = $fieldRegistry;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Get field type information
     *
     * Returns an array in the form:
     *
     * <code>
     *  array(
     *      "content-type-identifier" => array(
     *          "field-definition-identifier" => "field-type-identifier",
     *          …
     *      ),
     *      …
     *  )
     * </code>
     *
     * @return array
     */
    protected function getFieldMap()
    {
        // @TODO: temp fixed by disabling caching, see https://jira.ez.no/browse/EZP-22834
        $this->fieldTypes = array();

        foreach ( $this->contentTypeHandler->loadAllGroups() as $group )
        {
            foreach ( $this->contentTypeHandler->loadContentTypes( $group->id ) as $contentType )
            {
                foreach ( $contentType->fieldDefinitions as $fieldDefinition )
                {
                    if ( !$fieldDefinition->isSearchable )
                    {
                        continue;
                    }

                    $this->fieldTypes[$contentType->identifier][$fieldDefinition->identifier] =
                        $fieldDefinition->fieldType;
                }
            }
        }

        return $this->fieldTypes;
    }

    /**
     * For the given parameters returns a set of index storage field names to search on.
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
     * @param string $fieldTypeIdentifier
     * @param string $name
     *
     * @return array
     */
    public function getFieldNames(
        Criterion $criterion,
        $fieldDefinitionIdentifier,
        $fieldTypeIdentifier = null,
        $name = null
    )
    {
        $fieldMap = $this->getFieldMap();
        $fieldNames = array();

        foreach ( $fieldMap as $contentTypeIdentifier => $fieldIdentifierMap )
        {
            // First check if field exists in the current ContentType, there is nothing to do if it doesn't
            if ( !isset( $fieldIdentifierMap[$fieldDefinitionIdentifier] ) )
            {
                continue;
            }

            // If $fieldTypeIdentifier is given it must match current field definition
            if (
                $fieldTypeIdentifier !== null &&
                $fieldTypeIdentifier !== $fieldIdentifierMap[$fieldDefinitionIdentifier]
            )
            {
                continue;
            }

            $fieldNames[] = $this->getIndexFieldName(
                $criterion,
                $contentTypeIdentifier,
                $fieldDefinitionIdentifier,
                $fieldIdentifierMap[$fieldDefinitionIdentifier],
                $name
            );
        }

        return $fieldNames;
    }

    /**
     * For the given parameters returns index storage field name to sort on or
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
     * @param string $name
     *
     * @return null|string
     */
    public function getSortFieldName(
        SortClause $sortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $name = null
    )
    {
        $fieldMap = $this->getFieldMap();

        // First check if field exists in type, there is nothing to do if it doesn't
        if ( !isset( $fieldMap[$contentTypeIdentifier][$fieldDefinitionIdentifier] ) )
        {
            return null;
        }

        return $this->getIndexFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier,
            $fieldMap[$contentTypeIdentifier][$fieldDefinitionIdentifier],
            $name
        );
    }

    /**
     * Returns index field name for the given parameters.
     *
     * @param object $criterionOrSortClause
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param string $fieldTypeIdentifier
     * @param string $name
     *
     * @return mixed|string
     */
    public function getIndexFieldName(
        $criterionOrSortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $fieldTypeIdentifier,
        $name
    )
    {
        // If criterion or sort clause implements CustomFieldInterface and custom field is set for
        // ContentType/FieldDefinition, return it
        if (
            $criterionOrSortClause instanceof CustomFieldInterface &&
            $customFieldName = $criterionOrSortClause->getCustomField(
                $contentTypeIdentifier,
                $fieldDefinitionIdentifier
            )
        )
        {
            return $customFieldName;
        }

        // Else, generate field name from field type's index definition

        $indexFieldType = $this->fieldRegistry->getType( $fieldTypeIdentifier );

        // If $name is not given use default search field name
        if ( $name === null )
        {
            $name = $indexFieldType->getDefaultField();
        }

        $indexDefinition = $indexFieldType->getIndexDefinition();

        // Should only happen by mistake, so let's throw if it does
        if ( !isset( $indexDefinition[$name] ) )
        {
            throw new RuntimeException(
                "Could not find '{$name}' field in '{$fieldTypeIdentifier}' field type's index definition"
            );
        }

        return $this->nameGenerator->getTypedName(
            $this->nameGenerator->getName(
                $name,
                $fieldDefinitionIdentifier,
                $contentTypeIdentifier
            ),
            $indexDefinition[$name]
        );
    }
}
