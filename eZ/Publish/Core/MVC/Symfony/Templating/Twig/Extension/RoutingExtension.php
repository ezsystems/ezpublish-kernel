<?php
/**
 * File containing the RoutingExtension class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class RoutingExtension extends Twig_Extension
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface
     */
    private $routeReferenceGenerator;

    public function __construct( RouteReferenceGeneratorInterface $routeReferenceGenerator )
    {
        $this->routeReferenceGenerator = $routeReferenceGenerator;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'ez_route',
                array( $this, 'getRouteReference' )
            )
        );
    }

    public function getName()
    {
        return 'ezpublish.routing';
    }

    /**
     * @param mixed $resource
     * @param array $params
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\RouteReference
     */
    public function getRouteReference( $resource = null, $params = array() )
    {
        return $this->routeReferenceGenerator->generate( $resource, $params );
    }
}
