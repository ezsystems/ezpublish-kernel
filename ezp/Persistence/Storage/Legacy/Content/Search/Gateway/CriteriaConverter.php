<?php
/**
 * File containing the EzcDatabase criteria converter class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway,
    ezp\Persistence\Content\Query\Criterion,
    ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta database component.
 */
class CriteriaConverter
{
    /**
     * Criterion handlers
     *
     * @var array(CriterionHandler)
     */
    protected $handler;

    /**
     * Construct from an optional array of Criterion handlers
     *
     * @param array $handler
     * @return void
     */
    public function __construct( array $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Generic converter of criteria into query fragments
     *
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
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

        throw new \RuntimeException( 'No conversion for criterion found.' );
    }
}

