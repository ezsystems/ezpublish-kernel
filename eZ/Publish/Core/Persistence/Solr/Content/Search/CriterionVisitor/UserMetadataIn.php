<?php
/**
 * File containing the UserMetadataIn class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the UserMetadata criterion
 */
class UserMetadataIn extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\UserMetadata &&
            ( ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
              $criterion->operator === Operator::EQ );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        switch ( $criterion->target )
        {
            case Criterion\UserMetadata::MODIFIER:
                $solrField = 'creator_id';
                break;
            case Criterion\UserMetadata::OWNER:
                $solrField = 'owner_id';
                break;
            case Criterion\UserMetadata::GROUP:
            default:
                throw new NotImplementedException(
                    "No visitor available for: " . get_class( $criterion ) . ' with operator ' . $criterion->operator
                );
        }
        return '(' .
            implode(
                ' OR ',
                array_map(
                    function ( $value ) use ( $solrField )
                    {
                        return "{$solrField}:\"{$value}\"";
                    },
                    $criterion->value
                )
            ) .
            ')';
    }
}

