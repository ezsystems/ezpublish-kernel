<?php
/**
 * File containing the Controller class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use Symfony\Component\DependencyInjection\ContainerAware,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\Response;

abstract class Controller extends ContainerAware
{
    private $options;

    public function __construct( array $options = array() )
    {
        $this->options = $options;
    }

    /**
     * Returns value for $optionName and fallbacks to $defaultValue if not defined
     *
     * @param string $optionName
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getOption( $optionName, $defaultValue = null )
    {
        return isset( $this->options[$optionName] ) ? $this->options[$optionName] : $defaultValue;
    }

    /**
     * Checks if $optionName is defined
     *
     * @param string $optionName
     * @return bool
     */
    public function hasOption( $optionName )
    {
        return isset( $this->options[$optionName] );
    }

    /**
     * Sets $optionName with $value
     *
     * @param string $optionName
     * @param mixed $value
     */
    public function setOption( $optionName, $value )
    {
        $this->options[$optionName] = $value;
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

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->container->get( 'event_dispatcher' );
    }
}
