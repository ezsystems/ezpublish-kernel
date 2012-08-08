<?php
/**
 * File containing the RequestEventListener class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\HttpKernel\KernelEvents,
    Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Bundle\FrameworkBundle\HttpKernel,
    Symfony\Component\HttpKernel\Log\LoggerInterface,
    Symfony\Component\HttpFoundation\RedirectResponse,
    eZ\Publish\MVC\SiteAccess,
    eZ\Publish\MVC\SiteAccess\URILexer;

class RequestEventListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\HttpKernel
     */
    private $httpKernel;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;

    public function __construct( HttpKernel $httpKernel, LoggerInterface $logger = null )
    {
        $this->httpKernel = $httpKernel;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array( 'onKernelRequestForward', 10 ),
                array( 'onKernelRequestRedirect', 0 )
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequestForward( GetResponseEvent $event )
    {
        if ( $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST )
        {
            $request = $event->getRequest();
            if ( $request->attributes->get( 'needsForward' ) && $request->attributes->has( 'semanticPathinfo' ) )
            {
                $semanticPathinfo = $request->attributes->get( 'semanticPathinfo' );
                $event->setResponse(
                    $this->httpKernel->render( $semanticPathinfo )
                );
                $event->stopPropagation();

                if ( isset( $this->logger ) )
                    $this->logger->info(
                        "URLAlias made request to be forwarded to $semanticPathinfo",
                        array( 'pathinfo' => $request->getPathInfo() )
                    );
            }
        }
    }

    /**
     * Checks if the request needs to be redirected and return a RedirectResponse in such case.
     * The request attributes "needsRedirect" and "semanticPathinfo" are originally set in the UrlAliasRouter.
     *
     * Note: The event propagation will be stopped to ensure that no response can be set later and override the redirection.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @see \eZ\Publish\MVC\Routing\UrlAliasRouter
     */
    public function onKernelRequestRedirect( GetResponseEvent $event )
    {
        if ( $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST )
        {
            $request = $event->getRequest();
            if ( $request->attributes->get( 'needsRedirect' ) && $request->attributes->has( 'semanticPathinfo' ) )
            {
                $siteaccess = $request->attributes->get( 'siteaccess' );
                $semanticPathinfo = $request->attributes->get( 'semanticPathinfo' );
                if ( $siteaccess instanceof SiteAccess && $siteaccess->matcher instanceof URILexer )
                    $semanticPathinfo = $siteaccess->matcher->analyseLink( $semanticPathinfo );

                $event->setResponse(
                    new RedirectResponse(
                        $semanticPathinfo,
                        301
                    )
                );
                $event->stopPropagation();

                if ( isset( $this->logger ) )
                    $this->logger->info(
                        "URLAlias made request to be redirected to $semanticPathinfo",
                        array( 'pathinfo' => $request->getPathInfo() )
                    );
            }
        }
    }
}
