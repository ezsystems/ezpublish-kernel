<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;

/**
 * Sets sort direction on a field value for a content query
 */
class Field extends SortClause implements CustomFieldInterface
{
    /**
     * Custom fields to sort by instead of the default field
     *
     * @var array
     */
    protected $customFields = array();

    /**
     * Constructs a new Field SortClause on Type $typeIdentifier and Field $fieldIdentifier
     *
     * @param string $typeIdentifier
     * @param string $fieldIdentifier
     * @param string $sortDirection
     * @param null|string $languageCode
     */
    public function __construct( $typeIdentifier, $fieldIdentifier, $sortDirection = Query::SORT_ASC, $languageCode = null )
    {
        parent::__construct(
            'field',
            $sortDirection,
            new FieldTarget( $typeIdentifier, $fieldIdentifier, $languageCode )
        );
    }

    /**
     * Set a custom field to sort by
     *
     * Set a custom field to sort by for a defined field in a defined type.
     *
     * @param string $type
     * @param string $field
     * @param string $customField
     *
     * @return void
     */
    public function setCustomField( $type, $field, $customField )
    {
        $this->customFields[$type][$field] = $customField;
    }

    /**
     * Return custom field
     *
     * If no custom field is set, return null
     *
     * @param string $type
     * @param string $field
     *
     * @return mixed
     */
    public function getCustomField( $type, $field )
    {
        if ( !isset( $this->customFields[$type][$field] ) )
        {
            return null;
        }

        return $this->customFields[$type][$field];
    }
}
