<?php
/**
 * File containing the Controller class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\Controller;

use Symfony\Component\DependencyInjection\ContainerAware,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Routing\RouterInterface,
    Symfony\Component\HttpFoundation\Response;

abstract class Controller extends ContainerAware
{
    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render( $view, array $parameters = array(), Response $response = null )
    {
        if ( !isset( $response ) )
        {
            $response = new Response();
        }

        $response->setContent( $this->getTemplateEngine()->render( $view, $parameters ) );
        return $response;
    }

    /**
     * @return \Symfony\Component\Templating\EngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->container->get( 'templating' );
    }

    /**
     * @return \Symfony\Component\HttpKernel\Log\LoggerInterface\null
     */
    public function getLogger()
    {
        return $this->container->get( 'logger', ContainerInterface::NULL_ON_INVALID_REFERENCE );
    }

    /**
     * @return \eZ\Publish\API\Repository
     */
    public function getRepository()
    {
        return $this->container->get( 'ezpublish.api.repository' );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->container->get( 'request' );
    }
}
