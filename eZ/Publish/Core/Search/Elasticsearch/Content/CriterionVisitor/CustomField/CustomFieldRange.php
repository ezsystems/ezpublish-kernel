<?php

/**
 * File containing the CustomFieldRange Field criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\CustomField;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\CustomField;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the CustomField criterion with range operators (LT, LTE, GT, GTE and BETWEEN).
 *
 * @todo needs tests
 */
class CustomFieldRange extends CustomField
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
            $criterion instanceof Criterion\CustomField &&
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
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return array
     */
    protected function getCondition(Criterion $criterion)
    {
        $values = (array)$criterion->value;
        $start = $values[0];
        $end = isset($values[1]) ? $values[1] : null;
        if (($criterion->operator === Operator::LT) || ($criterion->operator === Operator::LTE)) {
            $end = $start;
            $start = null;
        }

        $range = $this->getQueryRange($criterion->operator, $start, $end);

        return [
            'query_string' => [
                'query' => 'fields_doc.' . $criterion->target . ':' . $range,
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
}
