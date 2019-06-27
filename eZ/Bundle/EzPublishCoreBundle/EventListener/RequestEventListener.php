<?php

/**
 * File containing the RequestEventListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class RequestEventListener implements EventSubscriberInterface
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var string */
    private $defaultSiteAccess;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    public function __construct(ConfigResolverInterface $configResolver, RouterInterface $router, $defaultSiteAccess, LoggerInterface $logger = null)
    {
        $this->configResolver = $configResolver;
        $this->defaultSiteAccess = $defaultSiteAccess;
        $this->router = $router;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestForward', 10],
                ['onKernelRequestRedirect', 0],
            ],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequestForward(GetResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $request = $event->getRequest();
            if ($request->attributes->get('needsForward') && $request->attributes->has('semanticPathinfo')) {
                $semanticPathinfo = $request->attributes->get('semanticPathinfo');
                $request->attributes->remove('needsForward');
                $forwardRequest = Request::create(
                    $semanticPathinfo,
                    $request->getMethod(),
                    $request->getMethod() === 'POST' ? $request->request->all() : $request->query->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    $request->getContent()
                );
                $forwardRequest->attributes->add($request->attributes->all());
                if ($request->headers->has('X-User-Hash')) {
                    $forwardRequest->headers->set('X-User-Hash', $request->headers->get('X-User-Hash'));
                }
                // Not forcing HttpKernelInterface::SUB_REQUEST on purpose since we're very early here
                // and we need to bootstrap essential stuff like sessions.
                $event->setResponse($event->getKernel()->handle($forwardRequest));
                $event->stopPropagation();

                if (isset($this->logger)) {
                    $this->logger->info(
                        "URLAlias made request to be forwarded to $semanticPathinfo",
                        ['pathinfo' => $request->getPathInfo()]
                    );
                }
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
    public function onKernelRequestRedirect(GetResponseEvent $event)
    {
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $request = $event->getRequest();
            if ($request->attributes->get('needsRedirect') && $request->attributes->has('semanticPathinfo')) {
                $siteaccess = $request->attributes->get('siteaccess');
                $semanticPathinfo = $request->attributes->get('semanticPathinfo');
                $queryString = $request->getQueryString();
                if (
                    $request->attributes->get('prependSiteaccessOnRedirect', true)
                    && $siteaccess instanceof SiteAccess
                    && $siteaccess->matcher instanceof URILexer
                ) {
                    $semanticPathinfo = $siteaccess->matcher->analyseLink($semanticPathinfo);
                }

                $headers = [];
                if ($request->attributes->has('locationId')) {
                    $headers['X-Location-Id'] = $request->attributes->get('locationId');
                }
                $event->setResponse(
                    new RedirectResponse(
                        $semanticPathinfo . ($queryString ? "?$queryString" : ''),
                        301,
                        $headers
                    )
                );
                $event->stopPropagation();

                if (isset($this->logger)) {
                    $this->logger->info(
                        "URLAlias made request to be redirected to $semanticPathinfo",
                        ['pathinfo' => $request->getPathInfo()]
                    );
                }
            }
        }
    }
}
