<?php
/**
 * File containing the RequestEventListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Bundle\EzPublishCoreBundle\Kernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Symfony\Component\HttpFoundation\Request;

class RequestEventListener implements EventSubscriberInterface
{
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
                array( 'onKernelRequestRedirect', 0 ),
                // onKernelRequestUserHash needs to be just after Firewall (prio 8), so that user is already logged in the repository.
                array( 'onKernelRequestUserHash', 7 ),
                // onKernelRequestIndex needs to be before the router (prio 32)
                array( 'onKernelRequestIndex', 40 ),
            )
        );
    }

    /**
     * Checks if the IndexPage is configured and which page must be shown
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequestIndex( GetResponseEvent $event )
    {
        /** @var $configResolver \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ContainerInterface.php */
        $configResolver = $this->container->get( 'ezpublish.config.resolver' );
        $request = $event->getRequest();
        $semanticPathinfo = $request->attributes->get( 'semanticPathinfo' ) ?: '/';
        if (
            $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST
            && $semanticPathinfo === '/'
        )
        {
            $indexPage = $configResolver->getParameter( 'index_page' );
            if ( $indexPage !== null )
            {
                $indexPage = '/' . ltrim( $indexPage, '/' );
                $request->attributes->set( 'semanticPathinfo', $indexPage );
                $request->attributes->set( 'needsForward', true );
            }
        }
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
        if ( $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST )
        {
            $request = $event->getRequest();
            if ( $request->attributes->get( 'needsForward' ) && $request->attributes->has( 'semanticPathinfo' ) )
            {
                $semanticPathinfo = $request->attributes->get( 'semanticPathinfo' );
                $request->attributes->remove( 'needsForward' );
                $forwardRequest = Request::create(
                    $semanticPathinfo,
                    $request->getMethod(),
                    $request->getMethod() === 'POST' ? $request->request->all() : $request->query->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    $request->getContent()
                );
                $forwardRequest->attributes->add( $request->attributes->all() );
                // Not forcing HttpKernelInterface::SUB_REQUEST on purpose since we're very early here
                // and we need to bootstrap essential stuff like sessions.
                $event->setResponse( $event->getKernel()->handle( $forwardRequest ) );
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
                $queryString = $request->getQueryString();
                if (
                    $request->attributes->get( 'prependSiteaccessOnRedirect', true )
                    && $siteaccess instanceof SiteAccess
                    && $siteaccess->matcher instanceof URILexer
                )
                {
                    $semanticPathinfo = $siteaccess->matcher->analyseLink( $semanticPathinfo );
                }

                $event->setResponse(
                    new RedirectResponse(
                        $semanticPathinfo . ( $queryString ? "?$queryString" : '' ),
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

    /**
     * Returns a Response containing the current user hash if needed.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequestUserHash( GetResponseEvent $event )
    {
        $request = $event->getRequest();

        if (
            $request->headers->get( 'X-HTTP-Override' ) !== 'AUTHENTICATE'
            || $request->headers->get( 'Accept' ) !== Kernel::USER_HASH_ACCEPT_HEADER
        )
        {
            return;
        }

        // We must have a session at that point since we're supposed to be connected
        if ( !$request->hasSession() )
        {
            $event->setResponse( new Response( '', 400 ) );
            $event->stopPropagation();
            return;
        }

        /** @var $hashGenerator \eZ\Publish\SPI\HashGenerator */
        $hashGenerator = $this->container->get( 'ezpublish.user.hash_generator' );
        $userHash = $hashGenerator->generate();
        $this->container->get( 'logger' )->debug( "UserHash: $userHash" );

        $response = new Response();
        $response->headers->set( 'X-User-Hash', $userHash );
        $event->setResponse( $response );
        $event->stopPropagation();
    }
}
