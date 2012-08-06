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
    eZ\Publish\MVC\SiteAccess\URILexer,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SiteAccess match listener.
 */
class SiteAccessListener implements EventSubscriberInterface
{
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
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 255 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        $siteAccess = $event->getSiteAccess();
        // Analyse the pathinfo if needed since it might contain the siteaccess (i.e. like in URI mode)
        $pathinfo = $event->getRequest()->getPathInfo();
        if ( $siteAccess->matcher instanceof URILexer )
        {
            $semanticPathinfo = $siteAccess->matcher->analyseURI( $pathinfo );
        }
        else
        {
            $semanticPathinfo = $pathinfo;
        }

        // Storing the modified pathinfo in 'semanticPathinfo' request attribute, to keep a trace of it.
        // Routers implementing RequestMatcherInterface should thus use this attribute instead of the original pathinfo
        $event->getRequest()->attributes->set(
            'semanticPathinfo',
            $semanticPathinfo
        );
    }
}
