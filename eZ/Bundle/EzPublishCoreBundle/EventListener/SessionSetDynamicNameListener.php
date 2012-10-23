<?php
/**
 * File containing the SessionSetDynamicNameListener class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\MVCEvents,
    eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * SiteAccess match listener.
 *
 * Allows to set a dynamic session name based on the siteaccess name.
 */
class SessionSetDynamicNameListener implements EventSubscriberInterface
{
    const MARKER = "{siteaccess_hash}";

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @note Injecting the service container is mandatory since event listeners are instantiated before siteaccess matching
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 250 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        if ( !$this->container->has( 'session' ) )
        {
            return;
        }

        // Getting from the container and not from the request because the session object is assigned to the request only when session has started.
        $session = $this->container->get( 'session' );


        /** @var $configResolver \eZ\Publish\Core\MVC\ConfigResolverInterface */
        $configResolver = $this->container->get( 'ezpublish.config.resolver' );
        $sessionName = $configResolver->getParameter( 'session_name' );
        if ( strpos( $sessionName, self::MARKER ) !== false )
        {
            $session->setName(
                str_replace(
                    self::MARKER, md5( $event->getSiteAccess()->name ), $sessionName
                )
            );
        }
        else
        {
            $session->setName( $sessionName );
        }
    }
}
