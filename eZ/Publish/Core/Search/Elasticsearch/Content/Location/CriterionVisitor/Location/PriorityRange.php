<?php

/**
 * File containing the range PriorityRange Priority criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Location\CriterionVisitor\Location;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the Priority criterion with range operators (LT, LTE, GT, GTE and BETWEEN).
 */
class PriorityRange extends CriterionVisitor
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
            $criterion instanceof Criterion\Matcher\Location\Priority &&
            (
                $criterion->operator === Operator::LT ||
                $criterion->operator === Operator::LTE ||
                $criterion->operator === Operator::GT ||
                $criterion->operator === Operator::GTE ||
                $criterion->operator === Operator::BETWEEN
            );
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
        /** @var Criterion\Matcher\Location\Priority $criterion */
        $start = $criterion->value[0];
        $end = isset($criterion->value[1]) ? $criterion->value[1] : null;

        return array(
            'range' => array(
                'priority_i' => $this->getFilterRange($criterion->operator, $start, $end),
            ),
        );
    }
}
