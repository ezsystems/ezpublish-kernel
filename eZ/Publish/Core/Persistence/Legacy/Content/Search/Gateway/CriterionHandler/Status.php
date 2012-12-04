<?php
/**
 * File containing the EzcDatabase status criterion handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    ezcQuerySelect;

/**
 * Status criterion handler
 */
class Status extends CriterionHandler
{
    /**
     * Mapping of status constants to status flags used in the database
     *
     * @var array
     */
    protected $statusMap = array(
        Criterion\Status::STATUS_DRAFT => 0,
        Criterion\Status::STATUS_PUBLISHED => 1,
        Criterion\Status::STATUS_ARCHIVED => 2,
    );

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\Status;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter$converter
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     *
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
    {
        $status = array();
        foreach ( $criterion->value as $value )
        {
            $status[] = $this->statusMap[$value];
        }

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'status', 'ezcontentobject' ),
            $status
        );
    }
}

