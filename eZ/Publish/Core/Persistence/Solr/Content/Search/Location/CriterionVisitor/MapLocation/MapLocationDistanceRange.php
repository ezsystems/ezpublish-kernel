<?php
/**
 * File containing the MapLocationDistanceRange criterion visitor class for location search
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Location\CriterionVisitor\MapLocation;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\MapLocation\MapLocationDistanceRange as ContentMapLocationDistanceRange;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the MapLocationDistance criterion
 */
class MapLocationDistanceRange extends ContentMapLocationDistanceRange
{
    /**
     * Map field value to a proper Solr representation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        return "{!child of='document_type_id:content' v='document_type_id:content AND " . parent::visit( $criterion, $subVisitor ) . "'}";
    }
}
