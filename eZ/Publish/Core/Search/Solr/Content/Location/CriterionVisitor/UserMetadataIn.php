<?php
/**
 * File containing the UserMetadataIn class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Location\CriterionVisitor;

use eZ\Publish\Core\Search\Solr\Content\CriterionVisitor;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Solr\Content\CriterionVisitor $subVisitor
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
                $solrField = 'owner_user_group_mid';
                break;

            default:
                throw new NotImplementedException(
                    "No visitor available for target: " . $criterion->target . ' with operator: ' . $criterion->operator
                );
        }

        $condition = implode(
            ' OR ',
            array_map(
                function ( $value ) use ( $solrField )
                {
                    return "{$solrField}:\"{$value}\"";
                },
                $criterion->value
            )
        );

        return "{!child of='document_type_id:content' v='{$condition}'}";
    }
}

