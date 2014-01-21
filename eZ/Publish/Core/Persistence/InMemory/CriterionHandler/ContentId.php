<?php
/**
 * File containing the InMemory content id criterion handler class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\CriterionHandler;

use eZ\Publish\Core\Persistence\InMemory\CriterionHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * Content id criterion handler
 */
class ContentId extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\ContentId;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $match
     * @param array $excludeMatch
     */
    public function handle( Criterion $criterion, array &$match, array &$excludeMatch )
    {
        if ( isset( $match["contentId"] ) )
            throw new RuntimeException( "A content ID criterion already exists" );

        $match["contentId"] = $criterion->value;
    }
}
