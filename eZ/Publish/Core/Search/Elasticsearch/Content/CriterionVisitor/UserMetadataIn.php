<?php

/**
 * File containing the UserMetadataIn criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the UserMetadata criterion.
 */
class UserMetadataIn extends CriterionVisitor
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
            $criterion instanceof Criterion\UserMetadata &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitFilter(Criterion $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        switch ($criterion->target) {
            case Criterion\UserMetadata::MODIFIER:
                $fieldName = 'creator_id';
                break;

            case Criterion\UserMetadata::OWNER:
                $fieldName = 'owner_id';
                break;

            case Criterion\UserMetadata::GROUP:
                $fieldName = 'owner_user_group_mid';
                break;

            default:
                throw new NotImplementedException(
                    'No visitor available for: ' . get_class($criterion) . " with target '{$criterion->target}'"
                );
        }

        if (count($criterion->value) > 1) {
            $filter = [
                'terms' => [
                    $fieldName => $criterion->value,
                ],
            ];
        } else {
            $filter = [
                'term' => [
                    $fieldName => $criterion->value[0],
                ],
            ];
        }

        return $filter;
    }
}
