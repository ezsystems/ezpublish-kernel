<?php
/**
 * File containing a Field criterion visitor class for Location Search
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Location\CriterionVisitor\Field;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\Field\FieldIn as ContentFieldIn;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the Field criterion
 */
class FieldIn extends ContentFieldIn
{
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
        return "{!child of='document_type_id:content' v='" . parent::visit( $criterion, $subVisitor ) . "'}";
    }
}

