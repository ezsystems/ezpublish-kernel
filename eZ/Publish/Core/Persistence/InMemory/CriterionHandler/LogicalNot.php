<?php
/**
 * File containing the InMemory logical NOT criterion handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\CriterionHandler;

use eZ\Publish\Core\Persistence\InMemory\CriterionHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Logical NOT criterion handler
 */
class LogicalNot extends CriterionHandler
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
        return $criterion instanceof Criterion\LogicalNot;
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
        $results = array();
        foreach ( $criterion->criteria as $subCriterion )
        {
            $innerMatch = $innerExcludeMatch = array();
            $this->locationHandler->convertCriteria( $subCriterion, $innerMatch, $innerExcludeMatch );
            $results[] = $result = $this->backend->find(
                "Content\\Location",
                $innerMatch,
                $innerExcludeMatch
            );
        }
        // Transform the 2 dimensional $results array in a 2 dimensional array of ids
        $mapping = array_map(
            function ( $n )
            {
                return array_map(
                    function ( $n )
                    {
                        return $n->id;
                    },
                    $n
                );
            },
            $results
        );

        // If some location IDs have been specified, those need to be taken into account as well
        if ( isset( $excludeMatch["id"] ) )
        {
            $mapping[] = (array)$excludeMatch["id"];
        }

        $locationIds = $mapping[0];

        for ( $i = 1, $count = count( $mapping ); $i < $count; ++$i )
        {
            $locationIds = array_intersect( $locationIds, $mapping[$i] );
        }

        $excludeMatch["id"] = $locationIds;
    }
}
