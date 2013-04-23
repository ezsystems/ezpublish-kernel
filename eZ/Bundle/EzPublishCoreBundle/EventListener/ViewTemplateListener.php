<?php
/**
 * File containing the ViewTemplateListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Bundle\EzPublishCoreBundle\Templating\ViewParameterProvider;
use eZ\Bundle\EzPublishCoreBundle\Templating\ParameterWrapper;
use InvalidArgumentException;

class ViewTemplateListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::PRE_CONTENT_VIEW => array(
                array( 'onPreContentView', 15 )
            )
        );
    }

    /**
     * Injects additional parameters to the selected view template, depending on the config hash used to match the template.
     * Services can be used to provide those parameters. They must either implement eZ\Bundle\EzPublishCoreBundle\Templating\ViewParameterProvider
     * or a method must be provided by configuration.
     *
     * @see \eZ\Publish\Core\MVC\Symfony\View\Manager::renderContentView()
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent $event
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function onPreContentView( PreContentViewEvent $event )
    {
        $contentView = $event->getContentView();
        $configHash = $contentView->getConfigHash();
        if ( $configHash !== null && isset( $configHash['params'] ) )
        {
            $configResolver = $this->container->get( 'ezpublish.config.resolver' );
            foreach ( $configHash['params'] as &$param )
            {
                // Resolve config resolver parameters
                // Supported syntax for parameters: $<paramName>[;<namespace>[;<scope>]]$
                if ( is_string( $param ) && strpos( $param, '$' ) === 0 && substr( $param, -1 ) === '$' )
                {
                    $configResolverParams = explode( ';', substr( $param, 1, -1 ) );
                    if ( count( $configResolverParams ) > 3 )
                    {
                        throw new \LogicException( "Config resolver parameters can't have more than 3 segments: \$paramName;namespace;scope\$" );
                    }

                    $namespace = isset( $configResolverParams[1] ) ? $configResolverParams[1] : null;
                    $scope = isset( $configResolverParams[2] ) ? $configResolverParams[2] : null;
                    $param = $configResolver->getParameter( $configResolverParams[0], $namespace, $scope );
                }
                else if ( is_array( $param ) && isset( $param['service'] ) )
                {
                    $parameterProvider = $this->container->get( $param['service'] );
                    if ( !$parameterProvider instanceof ViewParameterProvider && !isset( $param['method'] ) )
                        throw new InvalidArgumentException(
                            'Parameter provider service for view templates must either implement eZ\\Bundle\\EzPublishCoreBundle\\Templating\\ViewParameterProvider ' .
                            'or provide a method to call.'
                        );

                    // Use the provided method in priority if any.
                    if ( isset( $param['method'] ) )
                    {
                        $param = new ParameterWrapper( $parameterProvider->$param['method']( $contentView ) );
                    }
                    else
                    {
                        $param = new ParameterWrapper( $parameterProvider->getContentViewParameters( $contentView ) );
                    }
                }
            }

            $contentView->addParameters( $configHash['params'] );
        }
    }
}
