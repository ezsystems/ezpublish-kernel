<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\User\UserGateway;
use ezp\Persistence\LegacyStorage\User\UserGateway;

/**
 * Base class for content type gateways.
 */
class EzcDatabase extends UserGateway
{
    /**
     * Database handler
     * 
     * @var \ezcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     * 
     * @param \ezcDbHandler $handler 
     * @return void
     */
    public function __construct( \ezcDbHandler $handler )
    {
        $this->handler = $handler;
    }
}
