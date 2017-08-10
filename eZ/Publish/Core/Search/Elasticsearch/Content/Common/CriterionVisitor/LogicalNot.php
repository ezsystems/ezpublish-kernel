<?php

/**
 * File containing the LogicalNot criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Common\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * Visits the LogicalNot criterion.
 */
class LogicalNot extends CriterionVisitor
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
        return $criterion instanceof Criterion\LogicalOperator\LogicalNot;
    }

    /**
     * Validates criterion.
     *
     * @throws \RuntimeException
     *
     * @param CriterionInterface $criterion
     */
    protected function validateCriterionInput(CriterionInterface $criterion)
    {
        /** @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator\LogicalOperator */
        if (!isset($criterion->criteria[0]) || (count($criterion->criteria) > 1)) {
            throw new RuntimeException('Invalid aggregation in LogicalNot criterion.');
        }
    }

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @param CriterionInterface $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitFilter(CriterionInterface $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $this->validateCriterionInput($criterion);

        /* @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator\LogicalOperator */
        return array(
            'not' => $dispatcher->dispatch($criterion->criteria[0], Dispatcher::CONTEXT_FILTER, $languageFilter),
        );
    }

    /**
     * Map field value to a proper Elasticsearch query representation.
     *
     * @param CriterionInterface $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitQuery(CriterionInterface $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $this->validateCriterionInput($criterion);

        /* @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator\LogicalOperator */
        return array(
            'bool' => array(
                'must_not' => $dispatcher->dispatch(
                    $criterion->criteria[0],
                    Dispatcher::CONTEXT_FILTER,
                    $languageFilter
                ),
            ),
        );
    }
}
