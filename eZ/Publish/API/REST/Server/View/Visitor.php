<?php
/**
 * File containing the Visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\View;
use eZ\Publish\API\REST\Server;

use Qafoo\RMF;

/**
 * RMF visitor view
 */
class Visitor extends RMF\View
{
    /**
     * JSON visitor
     *
     * @var \eZ\Publish\API\REST\Server\Visitor
     */
    protected $visitor;

    /**
     * Construct
     *
     * @param Visitor $visitor
     * @return void
     */
    public function __construct( Server\Visitor $visitor )
    {
        $this->visitor = $visitor;
    }

    /**
     * Display the controller result
     *
     * @param RMF\Request $request
     * @param mixed $result
     * @return void
     */
    public function display( RMF\Request $request, $result )
    {
        $response = $this->visitor->visit( $result );

        foreach ( $response->headers as $name => $value )
        {
            header( "{$name}: {$value}" );
        }
        echo $response->body;
    }
}

