<?php

/**
 * File containing the CustomFieldIn Field criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\CustomField;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
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
     * @param CriterionInterface $criterion
     *
     * @return bool
     */
    public function canVisit(CriterionInterface $criterion)
    {
        return
            $criterion instanceof Criterion\Matcher\CustomField &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ ||
                $criterion->operator === Operator::CONTAINS
            );
    }

    /**
     * Returns nested condition common for filter and query contexts.
     *
     * @param CriterionInterface $criterion
     *
     * @return array
     */
    protected function getCondition(CriterionInterface $criterion)
    {
        /** @var Criterion\Matcher\CustomField $criterion */
        $terms = array();
        $values = (array)$criterion->value;

        foreach ($values as $value) {
            $terms[] = array(
                'match' => array(
                    'fields_doc.' . $criterion->target => array(
                        'query' => $value,
                    ),
                ),
            );
        }

        return $terms;
    }

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param CriterionInterface $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitFilter(CriterionInterface $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $filter = array(
            'nested' => array(
                'path' => 'fields_doc',
                'filter' => array(
                    'query' => array(
                        'bool' => array(
                            'should' => $this->getCondition($criterion),
                            'minimum_should_match' => 1,
                        ),
                    ),
                ),
            ),
        );

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter !== null) {
            $filter['nested']['filter'] = array(
                'bool' => array(
                    'must' => array(
                        $fieldFilter,
                        $filter['nested']['filter'],
                    ),
                ),
            );
        }

        return $filter;
    }
}
