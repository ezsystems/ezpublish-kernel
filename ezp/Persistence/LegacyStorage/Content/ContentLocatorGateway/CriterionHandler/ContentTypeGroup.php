<?php
/**
 * File containing the EzcDatabase content type group criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway\CriterionHandler;
use ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway\CriterionHandler,
    ezp\Persistence\LegacyStorage\Content\ContentLocatorGateway\CriteriaConverter,
    ezp\Persistence\Content\Criterion;

/**
 * Content type group criterion handler
 */
class ContentTypeGroup extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\ContentTypeGroup;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, \ezcQuerySelect $query, Criterion $criterion )
    {
        $subSelect = $query->subSelect();
        $subSelect
            ->select( 'contentclass_id' )
            ->from( 'ezcontentclass_classgroup' )
            ->where(
                $query->expr->in( 'group_id', $criterion->value )
            );

        return $query->expr->in( 'contentclass_id', $subSelect );
    }
}

