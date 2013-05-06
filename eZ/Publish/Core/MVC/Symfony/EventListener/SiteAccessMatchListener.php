<?php
/**
 * File containing the SiteAccessMatchListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router as SiteAccessRouter;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * kernel.request listener, triggers SiteAccess matching.
 * Should be triggered as early as possible.
 */
class SiteAccessMatchListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router
     */
    protected $siteAccessRouter;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct( SiteAccessRouter $siteAccessRouter, EventDispatcherInterface $eventDispatcher )
    {
        $this->siteAccessRouter = $siteAccessRouter;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return array(
            // Should take place just after FragmentListener (priority 48) in order to get rebuilt request attributes in case of subrequest
            KernelEvents::REQUEST => array( 'onKernelRequest', 45 ),
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest( GetResponseEvent $event )
    {
        $request = $event->getRequest();

        // We have a serialized siteaccess object from a fragment (sub-request), we need to get it back.
        if ( $request->attributes->has( 'serialized_siteaccess' ) )
        {
            $request->attributes->set(
                'siteaccess',
                unserialize( $request->attributes->get( 'serialized_siteaccess' ) )
            );
            $request->attributes->remove( 'serialized_siteaccess' );
        }
        else if ( !$request->attributes->has( 'siteaccess' ) )
        {
            $request->attributes->set(
                'siteaccess',
                $this->siteAccessRouter->match(
                    new SimplifiedRequest(
                        array(
                            'scheme'      => $request->getScheme(),
                            'host'        => $request->getHost(),
                            'port'        => $request->getPort(),
                            'pathinfo'    => $request->getPathInfo(),
                            'queryParams' => $request->query->all(),
                            'languages'   => $request->getLanguages(),
                            'headers'     => $request->headers->all()
                        )
                    )
                )
            );
        }

        $siteaccess = $request->attributes->get( 'siteaccess' );
        if ( $siteaccess instanceof SiteAccess )
        {
            $siteAccessEvent = new PostSiteAccessMatchEvent( $siteaccess, $request, $event->getRequestType() );
            $this->eventDispatcher->dispatch( MVCEvents::SITEACCESS, $siteAccessEvent );
        }
    }
}
