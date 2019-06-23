<?php

/**
 * File containing the CustomFieldIn Field criterion visitor class.
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
 * Visits the CustomField criterion with IN or EQ operator.
 */
class CustomFieldIn extends CustomField
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
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ ||
                $criterion->operator === Operator::CONTAINS
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
        $terms = [];
        $values = (array)$criterion->value;

        foreach ($values as $value) {
            $terms[] = [
                'match' => [
                    'fields_doc.' . $criterion->target => [
                        'query' => $value,
                    ],
                ],
            ];
        }

        return $terms;
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
                    'query' => [
                        'bool' => [
                            'should' => $this->getCondition($criterion),
                            'minimum_should_match' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter !== null) {
            $filter['nested']['filter'] = [
                'bool' => [
                    'must' => [
                        $fieldFilter,
                        $filter['nested']['filter'],
                    ],
                ],
            ];
        }

        return $filter;
    }
}
