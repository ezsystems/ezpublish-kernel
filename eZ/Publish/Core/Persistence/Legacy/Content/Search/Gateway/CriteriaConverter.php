<?php
/**
 * File containing the EzcDatabase criteria converter class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
 */
class CriteriaConverter
{
    /**
     * Criterion handlers
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler[]
     */
    protected $handler;

    /**
     * Construct from an optional array of Criterion handlers
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler[] $handler
     *
     * @return void
     */
    public function __construct( array $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Generic converter of criteria into query fragments
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException if criterion is not suppoerted
     *
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     *
     * @return \ezcQueryExpression
     */
    public function convertCriteria( ezcQuerySelect $query, Criterion $criterion )
    {
        foreach ( $this->handler as $handler )
        {
            if ( $handler->accept( $criterion ) )
            {
                return $handler->handle( $this, $query, $criterion );
            }
        }

        throw new NotImplementedException( "No visitor available for: " . get_class( $criterion ) . ' with operator ' . $criterion->operator );
    }
}
