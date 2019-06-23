<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\MapLocationDistance class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;

/**
 * Sets sort direction on the MapLocation distance for a content query.
 */
class MapLocationDistance extends SortClause implements CustomFieldInterface
{
    /**
     * Custom fields to sort by instead of the default field.
     *
     * @var array
     */
    protected $customFields = [];

    /**
     * Constructs a new MapLocationDistance SortClause on Type $typeIdentifier and Field $fieldIdentifier.
     *
     * @param string $typeIdentifier ContentType identifier
     * @param string $fieldIdentifier FieldDefinition identifier
     * @param float $latitude Latitude of the location that distance is calculated from
     * @param float $longitude Longitude of the location that distance is calculated from
     * @param string $sortDirection
     */
    public function __construct(
        $typeIdentifier,
        $fieldIdentifier,
        $latitude,
        $longitude,
        $sortDirection = Query::SORT_ASC
    ) {
        parent::__construct(
            'maplocation_distance',
            $sortDirection,
            new MapLocationTarget(
                $latitude,
                $longitude,
                $typeIdentifier,
                $fieldIdentifier
            )
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
        if (!isset($this->customFields[$type]) ||
            !isset($this->customFields[$type][$field])) {
            return null;
        }

        return $this->customFields[$type][$field];
    }
}
