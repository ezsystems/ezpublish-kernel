<?php
/**
 * File containing the RequestEventListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;

class RequestEventListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\HttpKernel
     */
    private $httpKernel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    public function __construct( ContainerInterface $container, LoggerInterface $logger = null )
    {
        $this->httpKernel = $container->get( 'http_kernel' );
        $this->container = $container;
        $this->logger = $logger;
        $this->router = $container->get( 'router' );
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array( 'onKernelRequestSetup', 190 ),
                array( 'onKernelRequestForward', 10 ),
                array( 'onKernelRequestRedirect', 0 )
            )
        );
    }

    /**
     * Checks if it's needed to redirect to setup wizard
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequestSetup( GetResponseEvent $event )
    {
        if (
            $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST
            && $this->container->hasParameter( 'ezpublish.siteaccess.default' )
        )
        {
            if ( $this->container->getParameter( 'ezpublish.siteaccess.default' ) !== 'setup' )
                return;

            $request = $event->getRequest();
            $requestContext = $this->container->get( 'router.request_context' );
            $requestContext->fromRequest( $request );
            $this->router->setContext( $requestContext );
            $setupURI = $this->router->generate( 'ezpublishSetup' );

            if ( ( $requestContext->getBaseUrl() . $request->getPathInfo() ) === $setupURI )
                return;

            $event->setResponse( new RedirectResponse( $setupURI ) );
        }
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
     *
     * @see \eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter
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
