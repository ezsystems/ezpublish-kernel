<?php

/**
 * File containing the ContentId criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Location\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the ContentId criterion.
 */
class ContentIdIn extends CriterionVisitor
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
            $criterion instanceof Criterion\Matcher\ContentId &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
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
        /** @var Criterion\Matcher\ContentId $criterion */
        return array(
            'terms' => array(
                'content_id_id' => $criterion->value,
            ),
        );
    }
}
