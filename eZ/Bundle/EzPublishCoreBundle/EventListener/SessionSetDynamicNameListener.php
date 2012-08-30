<?php
/**
 * File containing the SessionSetDynamicNameListener class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\MVC\MVCEvents,
    eZ\Publish\MVC\Event\PostSiteAccessMatchEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

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
        $siteAccess = $event->getSiteAccess();
        $session = $this->container->get( 'session' );
        $sessionName = $session->getName();
        if ( strpos( $sessionName, self::MARKER ) !== false )
        {
            $session->setName(
                str_replace(
                    self::MARKER, md5( $siteAccess->name ), $sessionName
                )
            );
        }
    }
}
