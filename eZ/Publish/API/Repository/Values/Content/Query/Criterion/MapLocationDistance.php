<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;

/**
 * The MapLocationDistance Criterion class.
 *
 * Provides content filtering based on distance from geographical location.
 */
class MapLocationDistance extends Criterion implements CustomFieldInterface
{
    /**
     * Custom field definitions to query instead of default field.
     *
     * @var array
     */
    protected $customFields = [];

    /**
     * @todo needs to be defined, could be a string identifying one of the predefined easing methods
     *
     * @var array
     */
    protected $boost;

    /**
     * @param string $target FieldDefinition identifier
     * @param string $operator One of the supported Operator constants
     * @param float|float[] $distance The match value in kilometers, either as an array
     *                                or as a single value, depending on the operator
     * @param float $latitude Latitude of the location that distance is calculated from
     * @param float $longitude Longitude of the location that distance is calculated from
     */
    public function __construct(string $target, string $operator, $distance, float $latitude, float $longitude)
    {
        $distanceStart = new MapLocationValue($latitude, $longitude);
        parent::__construct($target, $operator, $distance, $distanceStart);
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY),
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE),
            new Specifications(Operator::GT, Specifications::FORMAT_SINGLE),
            new Specifications(Operator::GTE, Specifications::FORMAT_SINGLE),
            new Specifications(Operator::LT, Specifications::FORMAT_SINGLE),
            new Specifications(Operator::LTE, Specifications::FORMAT_SINGLE),
            new Specifications(Operator::BETWEEN, Specifications::FORMAT_ARRAY, null, 2),
        ];
    }

    /**
     * Set a custom field to query.
     *
     * Set a custom field to query for a defined field in a defined type.
     *
     * @param string $type
     * @param string $field
     * @param string $customField
     */
    public function setCustomField(string $type, string $field, string $customField): void
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
     * @return ?string
     */
    public function getCustomField(string $type, string $field): ?string
    {
        if (!isset($this->customFields[$type]) ||
             !isset($this->customFields[$type][$field])) {
            return null;
        }

        return $this->customFields[$type][$field];
    }
}
