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
    Symfony\Component\HttpFoundation\Response,
    eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;

abstract class Controller extends ContainerAware
{
    /**
     * Returns value for $parameterName and fallbacks to $defaultValue if not defined
     *
     * @param string $parameterName
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getParameter( $parameterName, $defaultValue = null )
    {
        if ( $this->getConfigResolver()->hasParameter( $parameterName ) )
            return $this->getConfigResolver()->getParameter( $parameterName );

        return $defaultValue;
    }

    /**
     * Checks if $parameterName is defined
     *
     * @param string $parameterName
     * @return bool
     */
    public function hasParameter( $parameterName )
    {
        return $this->getConfigResolver()->hasParameter( $parameterName );
    }

    /**
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    public function getConfigResolver()
    {
        return $this->container->get( 'ezpublish.config.resolver' );
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
     * @return \Symfony\Component\HttpKernel\Log\LoggerInterface|null
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

    /**
     * Checks if current user has granted access to provided attribute
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute $attribute
     * @return bool
     */
    public function isGranted( AuthorizationAttribute $attribute )
    {
        return $this->container->get( 'security.context' )->isGranted( $attribute );
    }
}
