<?php
/**
 * File containing the MapLocationDistanceIn criterion visitor class for location search
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Location\CriterionVisitor\MapLocation;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\MapLocation\MapLocationDistanceIn as ContentMapLocationDistanceIn;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the MapLocationDistance criterion
 */
class MapLocationDistanceIn extends ContentMapLocationDistanceIn
{
    /**
     * Map field value to a proper Solr representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        return "{!child of='document_type_id:content' v='" . parent::visit( $criterion, $subVisitor ) . "'}";
    }
}

