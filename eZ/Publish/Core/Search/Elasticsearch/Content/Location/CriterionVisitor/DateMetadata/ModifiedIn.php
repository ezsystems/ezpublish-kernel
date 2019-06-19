<?php

/**
 * File containing the ModifiedBetween criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Location\CriterionVisitor\DateMetadata;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor\DateMetadata;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the ModifiedIn DateMetadata criterion.
 */
class ModifiedIn extends DateMetadata
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
            $criterion instanceof Criterion\DateMetadata &&
            $criterion->target === 'modified' &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
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
        if (count($criterion->value) > 1) {
            $that = $this;
            $filter = [
                'terms' => [
                    'content_modified_dt' => array_map(
                        function ($timestamp) use ($that) {
                            return $that->getNativeTime($timestamp);
                        },
                        $criterion->value
                    ),
                ],
            ];
        } else {
            $filter = [
                'term' => [
                    'content_modified_dt' => $this->getNativeTime($criterion->value[0]),
                ],
            ];
        }

        return $filter;
    }
}
