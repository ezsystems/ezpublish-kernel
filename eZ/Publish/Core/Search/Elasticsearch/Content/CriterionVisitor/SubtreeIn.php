<?php

/**
 * File containing the SubtreeIn criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the Subtree criterion.
 */
class SubtreeIn extends CriterionVisitor
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
            $criterion instanceof Criterion\Subtree &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
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
        $filters = array();

        /** @var Criterion\Subtree $criterion */
        foreach ($criterion->value as $value) {
            $filters[] = array(
                'prefix' => array(
                    'locations_doc.path_string_id' => $value,
                ),
            );
        }

        return $filters;
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
        return array(
            'nested' => array(
                'path' => 'locations_doc',
                'filter' => array(
                    'or' => $this->getCondition($criterion),
                ),
            ),
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
        return array(
            'nested' => array(
                'path' => 'locations_doc',
                'query' => array(
                    'bool' => array(
                        'should' => $this->getCondition($criterion),
                        'minimum_should_match' => 1,
                    ),
                ),
            ),
        );
    }
}
