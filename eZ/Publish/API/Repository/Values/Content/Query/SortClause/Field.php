<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;

/**
 * Sets sort direction on a field value for a content query.
 *
 * Note: for fields of some field types order will vary per search engine. This comes from the
 * different way of storing IDs in the search backend, and therefore relates to the field types
 * that store ID value for sorting (Relation field type). For Legacy search engine IDs are stored as
 * integers, while with Solr and Elasticsearch engines they are stored as strings. In that case the
 * difference will be basically the one between numerical and alphabetical order of sorting.
 *
 * This reflects API definition of IDs as mixed type (integer or string).
 */
class Field extends SortClause implements CustomFieldInterface
{
    /**
     * Custom fields to sort by instead of the default field.
     *
     * @var array
     */
    protected $customFields = [];

    /**
     * Constructs a new Field SortClause on Type $typeIdentifier and Field $fieldIdentifier.
     *
     * @param string $typeIdentifier
     * @param string $fieldIdentifier
     * @param string $sortDirection
     */
    public function __construct($typeIdentifier, $fieldIdentifier, $sortDirection = Query::SORT_ASC)
    {
        parent::__construct(
            'field',
            $sortDirection,
            new FieldTarget($typeIdentifier, $fieldIdentifier)
        );
    }

    /**
     * Set a custom field to sort by.
     *
     * Set a custom field to sort by for a defined field in a defined type.
     *
     * @param string $type
     * @param string $field
     * @param string $customField
     */
    public function setCustomField($type, $field, $customField)
    {
        $this->customFields[$type][$field] = $customField;
    }

    /**
     * Return custom field.
     *
     * If no custom field is set, return null
     *
     * @param string $type
     * @param string $field
     *
     * @return mixed
     */
    public function getCustomField($type, $field)
    {
        if (!isset($this->customFields[$type][$field])) {
            return null;
        }

        return $this->customFields[$type][$field];
    }
}
