<?php
/**
 * File containing the SiteAccessListener class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\MVC\MVCEvents,
    eZ\Publish\MVC\Event\PostSiteAccessMatchEvent,
    eZ\Publish\MVC\SiteAccess\URIFixer,
    Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SiteAccess match listener.
 */
class SiteAccessListener implements EventSubscriberInterface
{
    static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 255 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        $siteAccess = $event->getSiteAccess();
        if ( $siteAccess->matcher instanceof URIFixer )
        {
            $event->setPathinfo(
                $siteAccess->matcher->fixupURI( $event->getRequest()->getPathInfo() )
            );
        }
    }
}
