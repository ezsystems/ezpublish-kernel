<?php

/**
 * File containing the range FieldRange Field criterion visitor class.
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

/**
 * Visits the Field criterion with range operators (LT, LTE, GT, GTE and BETWEEN).
 */
class FieldRange extends Field
{
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
            $criterion instanceof Criterion\Field &&
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
        $fieldNames = $this->getFieldNames($criterion, $criterion->target);
        $value = (array)$criterion->value;

        if (empty($fieldNames)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $start = $value[0];
        $end = isset($value[1]) ? $value[1] : null;
        if (($criterion->operator === Operator::LT) || ($criterion->operator === Operator::LTE)) {
            $end = $start;
            $start = null;
        }

        $fields = [];
        foreach ($fieldNames as $name) {
            $fields[] = 'fields_doc.' . $name;
        }

        return [
            'query_string' => [
                'fields' => $fields,
                'query' => $this->getQueryRange($criterion->operator, $start, $end),
            ],
        ];
    }

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
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
                    'query' => $this->getCondition($criterion),
                ],
            ],
        ];

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter !== null) {
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
            'bool' => [
                'should' => $this->getCondition($criterion),
                'minimum_should_match' => 1,
            ],
        ];

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter === null) {
            $query = [
                'nested' => [
                    'path' => 'fields_doc',
                    'query' => $this->getCondition($criterion),
                ],
            ];
        } else {
            $query = [
                'nested' => [
                    'path' => 'fields_doc',
                    'query' => [
                        'filtered' => [
                            'query' => $this->getCondition($criterion),
                            'filter' => $fieldFilter,
                        ],
                    ],
                ],
            ];
        }

        return $query;
    }
}
