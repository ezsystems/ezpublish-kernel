<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This behavior is reflected in meta repository's vhost.template so it should
 * not be triggered on recommended nginx/apache setups. It mostly applies to
 * Platform.sh and setups not relying on recommended vhost configuration.
 */
class RejectExplicitFrontControllerRequestsListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 255],
            ],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $scriptFileName = preg_quote(basename($request->server->get('SCRIPT_FILENAME')), '\\');
        // This pattern has to match with vhost.template files in meta repository
        $pattern = sprintf('<^/([^/]+/)?%s([/?#]|$)>', $scriptFileName);

        if (1 === preg_match($pattern, $request->getRequestUri())) {
            // Trigger generic 404 error to avoid leaking backend technology details.
            throw new NotFoundHttpException();
        }
    }
}
