<?php
/**
 * File containing the Request class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server;

use Qafoo\RMF\Request\HTTP as RMFRequest;

/**
 * Encapsulated RMF HTTP Request for REST server
 */
class Request extends RMFRequest
{
    /**
     * Construct request from a set of handlers
     *
     * @param array $handlers
     *
     * @return \eZ\Publish\Core\REST\Server\Request
     */
    public function __construct( array $handlers = array() )
    {
        parent::__construct( $handlers );
    }
}
