<?php

/**
 * File containing the LogicalAnd criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Common\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the LogicalAnd criterion.
 */
class LogicalAnd extends CriterionVisitor
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
        return $criterion instanceof Criterion\LogicalAnd;
    }

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitFilter(Criterion $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        /* @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator */
        return [
            'and' => array_map(
                function ($value) use ($dispatcher, $languageFilter) {
                    return $dispatcher->dispatch($value, Dispatcher::CONTEXT_FILTER, $languageFilter);
                },
                $criterion->criteria
            ),
        ];
    }

    /**
     * Map field value to a proper Elasticsearch query representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitQuery(Criterion $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        /* @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator */
        return [
            'bool' => [
                'must' => [
                    array_map(
                        function ($value) use ($dispatcher, $languageFilter) {
                            return $dispatcher->dispatch($value, Dispatcher::CONTEXT_QUERY, $languageFilter);
                        },
                        $criterion->criteria
                    ),
                ],
            ],
        ];
    }
}
