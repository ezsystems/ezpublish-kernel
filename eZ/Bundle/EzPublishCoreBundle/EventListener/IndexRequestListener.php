<?php

/**
 * File containing the IndexRequestListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class IndexRequestListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                // onKernelRequestIndex needs to be before the router (prio 32)
                ['onKernelRequestIndex', 40],
            ],
        ];
    }

    /**
     * Checks if the IndexPage is configured and which page must be shown.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequestIndex(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $semanticPathinfo = $request->attributes->get('semanticPathinfo') ?: '/';
        if (
            $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST
            && $semanticPathinfo === '/'
        ) {
            $indexPage = $this->configResolver->getParameter('index_page');
            if ($indexPage !== null) {
                $indexPage = '/' . ltrim($indexPage, '/');
                $request->attributes->set('semanticPathinfo', $indexPage);
                $request->attributes->set('needsForward', true);
            }
        }
    }
}
