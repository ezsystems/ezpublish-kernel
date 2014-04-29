<?php
/**
 * File containing the ChainRouter class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing;

use Symfony\Cmf\Component\Routing\ChainRouter as BaseChainRouter;

class ChainRouter extends BaseChainRouter
{
    public function generate( $name, $parameters = array(), $absolute = false )
    {
        if ( $name instanceof RouteReference )
        {
            $parameters += $name->getParams();
            $name = $name->getRoute();
        }

        return parent::generate( $name, $parameters, $absolute );
    }
}
