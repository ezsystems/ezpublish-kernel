<?php

/**
 * File containing the OriginalRequestListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Request listener setting potential original request as current request attribute.
 * Such situation occurs when generating user context hash from an external reverse proxy (e.g. Varnish).
 */
class OriginalRequestListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 200],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->headers->has('x-fos-original-url')) {
            return;
        }

        $originalRequest = Request::create(
            $request->getSchemeAndHttpHost() . $request->headers->get('x-fos-original-url'),
            'GET',
            [],
            [],
            [],
            ['HTTP_ACCEPT' => $request->headers->get('x-fos-original-accept')]
        );
        $originalRequest->headers->set('user-agent', $request->headers->get('user-agent'));
        $originalRequest->headers->set('accept-language', $request->headers->get('accept-language'));
        $request->attributes->set('_ez_original_request', $originalRequest);
    }
}
