<?php
/**
 * File containing the Controller class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC;

use Symfony\Component\Templating\EngineInterface,
    Symfony\Component\HttpKernel\Log\LoggerInterface,
    Symfony\Component\Routing\RouterInterface,
    Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    /**
     * @var Symfony\Component\Templating\EngineInterface
     */
    protected $templateEngine;

    /**
     * @var Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected $logger;

    public function setTemplateEngine( EngineInterface $templateEngine )
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function setLogger( LoggerInterface $logger = null )
    {
        $this->logger = $logger;
    }

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

        $response->setContent( $this->templateEngine->render( $view, $parameters ) );
        return $response;
    }
}
