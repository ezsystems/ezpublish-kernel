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
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SiteAccess match listener.
 *
 * Allows to set a dynamic session name based on the siteaccess name.
 */
class SessionSetDynamicNameListener implements EventSubscriberInterface
{
    const MARKER = "{siteaccess_hash}";

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 250 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        $session = $event->getRequest()->getSession();
        if ( !$session )
        {
            return;
        }

        $siteAccess = $event->getSiteAccess();
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
