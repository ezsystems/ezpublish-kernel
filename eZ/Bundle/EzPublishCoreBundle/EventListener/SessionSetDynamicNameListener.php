<?php
/**
 * File containing the SessionSetDynamicNameListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->container->get( 'session' );

        if ( !$session->isStarted() )
        {
            $sessionName = $this->container->get( 'ezpublish.config.resolver' )->getParameter( 'session_name' );
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
}
