<?php
/**
 * File containing the InMemory logical OR criterion handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\CriterionHandler;

use eZ\Publish\Core\Persistence\InMemory\CriterionHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Logical OR criterion handler
 */
class LogicalOr extends CriterionHandler
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
        return $criterion instanceof Criterion\LogicalOr;
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
        $locationIds = array();
        foreach ( $criterion->criteria as $subCriterion )
        {
            $innerMatch = $innerExcludeMatch = array();
            $this->locationHandler->convertCriteria( $subCriterion, $innerMatch, $innerExcludeMatch );
            $results = $this->backend->find(
                "Content\\Location",
                $innerMatch,
                $innerExcludeMatch
            );
            if ( empty( $results ) )
            {
                continue;
            }

            foreach ( $results as $result )
            {
                $locationIds[$result->id] = true;
            }
        }

        // If some location IDs have been specified, those need to be taken into account as well
        if ( isset( $match["id"] ) )
        {
            $locationIds += array_fill_keys( (array)$match["id"], true);
        }

        $match["id"] = array_keys( $locationIds );
    }
}
