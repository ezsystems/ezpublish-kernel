<?php

/**
 * File containing the LanguageCodeIn criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the LanguageCode criterion.
 */
class LanguageCodeIn extends CriterionVisitor
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
            $criterion instanceof Criterion\LanguageCode &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
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
        if (count($criterion->value) > 1) {
            return [
                'terms' => [
                    'language_code_ms' => $criterion->value,
                ],
            ];
        } else {
            return [
                'term' => [
                    'language_code_ms' => $criterion->value[0],
                ],
            ];
        }
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
        $filter = $this->getCondition($criterion);

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode $criterion */
        if ($criterion->matchAlwaysAvailable) {
            $filter = [
                'or' => [
                    $filter,
                    [
                        'term' => [
                            'always_available_b' => true,
                        ],
                    ],
                ],
            ];
        }

        return $filter;
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
        $filter = $this->getCondition($criterion);

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode $criterion */
        if ($criterion->matchAlwaysAvailable) {
            $filter = [
                'bool' => [
                    'should' => [
                        $filter,
                        [
                            'term' => [
                                'always_available_b' => true,
                            ],
                        ],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];
        }

        return $filter;
    }
}
