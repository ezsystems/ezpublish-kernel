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
use ezcQuerySelect;
use RuntimeException;

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

        throw new RuntimeException( 'No conversion for criterion found.' );
    }
}

