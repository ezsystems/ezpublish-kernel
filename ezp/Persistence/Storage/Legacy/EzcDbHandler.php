<?php
/**
 * File containing a wrapper for the DB handler
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy;

/**
 * Wrapper class for the zeta components database handler, providing some
 * additional utility functions.
 *
 * Functions as a full proxy to the zeta components database class.
 *
 * @version //autogentag//
 */
class EzcDbHandler
{
    /**
     * Aggregated zeta compoenents database handler, which is target of the
     * method dispatching.
     *
     * @var \ezcDbHandler
     */
    protected $ezcDbHandler;

    /**
     * Construct from zeta components database handler
     *
     * @param \ezcDbHandler $ezcDbHandler
     * @return void
     */
    public function __construct( \ezcDbHandler $ezcDbHandler )
    {
        $this->ezcDbHandler = $ezcDbHandler;
    }

    public function __call( $method, $parameters )
    {
        return call_user_func_array( array( $this->ezcDbHandler, $method ), $parameters );
    }
}

