<?php

/**
 * File containing the FieldIn Field criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\Field;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the Field criterion with IN or EQ operator.
 */
class FieldIn extends Field
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
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ ||
                $criterion->operator === Operator::CONTAINS
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

        $values = (array)$criterion->value;

        if (empty($fieldNames)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $fields = array();
        foreach ($fieldNames as $name) {
            $fields[] = 'fields_doc.' . $name;
        }

        $terms = array();
        foreach ($values as $value) {
            $terms[] = array(
                'multi_match' => array(
                    'query' => $value,
                    'fields' => $fields,
                ),
            );
        }

        return array(
            'bool' => array(
                'should' => $terms,
                'minimum_should_match' => 1,
            ),
        );
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
        $filter = array(
            'nested' => array(
                'path' => 'fields_doc',
                'filter' => array(
                    'query' => $this->getCondition($criterion),
                ),
            ),
        );

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($languageFilter !== null) {
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
        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter === null) {
            $query = array(
                'nested' => array(
                    'path' => 'fields_doc',
                    'query' => $this->getCondition($criterion),
                ),
            );
        } else {
            $query = array(
                'nested' => array(
                    'path' => 'fields_doc',
                    'query' => array(
                        'filtered' => array(
                            'query' => $this->getCondition($criterion),
                            'filter' => $fieldFilter,
                        ),
                    ),
                ),
            );
        }

        return $query;
    }
}
