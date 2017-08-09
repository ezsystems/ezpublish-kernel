<?php

/**
 * File containing the LocationRemoteIdIn criterion visitor class.
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
 * Visits the LocationRemoteId criterion.
 */
class LocationRemoteIdIn extends CriterionVisitor
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
            $criterion instanceof Criterion\LocationRemoteId &&
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
        /** @var Criterion\LocationRemoteId $criterion */
        if (count($criterion->value) > 1) {
            return array(
                'terms' => array(
                    'locations_doc.remote_id_id' => $criterion->value,
                ),
            );
        } else {
            return array(
                'term' => array(
                    'locations_doc.remote_id_id' => $criterion->value[0],
                ),
            );
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
        return array(
            'nested' => array(
                'path' => 'locations_doc',
                'filter' => $this->getCondition($criterion),
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
                'query' => $this->getCondition($criterion),
            ),
        );
    }
}
