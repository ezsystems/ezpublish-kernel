<?php
/**
 * File containing the ModifiedBetween criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\CriterionVisitor\DateMetadata;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor\DateMetadata;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the ModifiedIn DateMetadata criterion
 */
class ModifiedIn extends DateMetadata
{
    /**
     * Check if visitor is applicable to current criterion
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\DateMetadata &&
            $criterion->target === "modified" &&
            (
                ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        if ( count( $criterion->value ) > 1 )
        {
            $that = $this;
            $filter = array(
                "terms" => array(
                    "content_modified_dt" => array_map(
                        function ( $timestamp ) use ( $that )
                        {
                            return $that->getNativeTime( $timestamp );
                        },
                        $criterion->value
                    ),
                ),
            );
        }
        else
        {
            $filter = array(
                "term" => array(
                    "content_modified_dt" => $this->getNativeTime( $criterion->value[0] ),
                ),
            );
        }

        return $filter;
    }
}
