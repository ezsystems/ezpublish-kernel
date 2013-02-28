<?php
/**
 * File containing the InMemory content type groupe id criterion handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\CriterionHandler;

use eZ\Publish\Core\Persistence\InMemory\CriterionHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Content type groupe id criterion handler
 */
class ContentTypeGroupId extends CriterionHandler
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
        return $criterion instanceof Criterion\ContentTypeGroupId;
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
        $contentType = $this->backend->find(
            "Content\\Type",
            array( "groupIds" => $criterion->value )
        );
        if ( empty( $contentType ) )
        {
            return false;
        }

        $results = $this->backend->find(
            "Content\\ContentInfo",
            array( "contentTypeId" => $contentType[0]->id )
        );
        if ( empty( $results ) )
        {
            return false;
        }

        $contentIds = array();
        foreach ( $results as $result )
        {
            $contentIds[$result->id] = true;
        }
        $match["contentId"] = array_keys( $contentIds );
    }
}
