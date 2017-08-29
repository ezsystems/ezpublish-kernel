<?php

/**
 * File containing the Visibility criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the Visibility criterion.
 */
class Visibility extends CriterionVisitor
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
        return $criterion instanceof Criterion\Matcher\Visibility && $criterion->operator === Operator::EQ;
    }

    /**
     * Map Criterion visibility value to a proper Elasticsearch representation.
     *
     * @param CriterionInterface $criterion
     *
     * @return bool
     */
    protected function getInternalValue(CriterionInterface $criterion)
    {
        /** @var Criterion\Matcher\Visibility $criterion */
        return $criterion->value[0] === Criterion\Matcher\Visibility::HIDDEN ? true : false;
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
                    'term' => array(
                        'invisible_b' => $this->getInternalValue($criterion),
                    ),
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
                    'term' => array(
                        'invisible_b' => $this->getInternalValue($criterion),
                    ),
                ),
            ),
        );
    }
}
