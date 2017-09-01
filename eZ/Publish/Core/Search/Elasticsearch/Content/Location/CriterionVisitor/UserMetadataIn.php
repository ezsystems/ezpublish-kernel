<?php

/**
 * File containing the UserMetadataIn criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Location\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the UserMetadata criterion.
 */
class UserMetadataIn extends CriterionVisitor
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
            $criterion instanceof Criterion\Matcher\UserMetadata &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param CriterionInterface $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitFilter(CriterionInterface $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        /** @var Criterion\Matcher\UserMetadata $criterion */
        switch ($criterion->target) {
            case Criterion\Matcher\UserMetadata::MODIFIER:
                $fieldName = 'content_creator_id';
                break;

            case Criterion\Matcher\UserMetadata::OWNER:
                $fieldName = 'content_owner_id';
                break;

            case Criterion\Matcher\UserMetadata::GROUP:
                $fieldName = 'content_owner_user_group_mid';
                break;

            default:
                throw new NotImplementedException(
                    'No visitor available for: ' . get_class($criterion) . " with target '{$criterion->target}'"
                );
        }

        if (count($criterion->value) > 1) {
            $filter = array(
                'terms' => array(
                    $fieldName => $criterion->value,
                ),
            );
        } else {
            $filter = array(
                'term' => array(
                    $fieldName => $criterion->value[0],
                ),
            );
        }

        return $filter;
    }
}
