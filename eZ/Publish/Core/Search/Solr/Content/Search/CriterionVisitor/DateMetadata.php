<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use Exception;

/**
 * Visits the DateMetadata criterion
 */
abstract class DateMetadata extends CriterionVisitor
{
    /**
     * Map value to a proper Solr date representation
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function getSolrTime( $value )
    {
        if ( is_numeric( $value ) )
        {
            $date = new \DateTime( "@{$value}" );
        }
        else
        {
            try
            {
                $date = new \DateTime( $value );
            }
            catch ( Exception $e )
            {
                throw new \InvalidArgumentException( "Invalid date provided: " . $value );
            }
        }

        return $date->format( "Y-m-d\\TH:i:s\\Z" );
    }
}

