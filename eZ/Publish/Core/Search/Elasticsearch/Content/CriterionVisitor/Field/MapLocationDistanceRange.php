<?php

/**
 * File containing the MapLocationDistanceRange criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\Field;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Common\FieldNameResolver;

/**
 * Visits the MapLocationDistance criterion.
 */
class MapLocationDistanceRange extends Field
{
    /**
     * Identifier of the field type that criterion can handle.
     *
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * Name of the field type's indexed field that criterion can handle.
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Create from FieldNameResolver, FieldType identifier and field name.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param string $fieldTypeIdentifier
     * @param string $fieldName
     */
    public function __construct(FieldNameResolver $fieldNameResolver, $fieldTypeIdentifier, $fieldName)
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;

        parent::__construct($fieldNameResolver);
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\MapLocationDistance &&
            (
                $criterion->operator === Operator::LT ||
                $criterion->operator === Operator::LTE ||
                $criterion->operator === Operator::GT ||
                $criterion->operator === Operator::GTE ||
                $criterion->operator === Operator::BETWEEN
            );
    }

    /**
     * Returns nested condition common for filter and query contexts.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return array
     */
    protected function getCondition(Criterion $criterion)
    {
        $criterion->value = (array)$criterion->value;

        $start = $criterion->value[0];
        $end = isset($criterion->value[1]) ? $criterion->value[1] : 63510;

        // Converting kilometers to meters, which is default distance unit in Elasticsearch
        $start *= 1000;
        $end *= 1000;

        $fieldNames = $this->getFieldNames(
            $criterion,
            $criterion->target,
            $this->fieldTypeIdentifier,
            $this->fieldName
        );

        if (empty($fieldNames)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;
        $range = $this->getFilterRange($criterion->operator, $start, $end);

        $filters = [];
        foreach ($fieldNames as $name) {
            $filter = $range;
            $filter["fields_doc.{$name}"] = [
                'lat' => $location->latitude,
                'lon' => $location->longitude,
            ];

            $filters[] = [
                'geo_distance_range' => $filter,
            ];
        }

        return $filters;
    }

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitFilter(Criterion $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $filter = [
            'nested' => [
                'path' => 'fields_doc',
                'filter' => [
                    'or' => $this->getCondition($criterion),
                ],
            ],
        ];

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($languageFilter !== null) {
            $filter['nested']['filter'] = [
                'and' => [
                    $fieldFilter,
                    $filter['nested']['filter'],
                ],
            ];
        }

        return $filter;
    }

    /**
     * Map field value to a proper Elasticsearch query representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitQuery(Criterion $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $query = [
            'filtered' => [
                'filter' => [
                    'or' => $this->getCondition($criterion),
                ],
            ],
        ];

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter === null) {
            $query = [
                'nested' => [
                    'path' => 'fields_doc',
                    'query' => $query,
                ],
            ];
        } else {
            $query = [
                'nested' => [
                    'path' => 'fields_doc',
                    'query' => [
                        'filtered' => [
                            'query' => $query,
                            'filter' => $fieldFilter,
                        ],
                    ],
                ],
            ];
        }

        return $query;
    }
}
