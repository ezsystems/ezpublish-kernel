<?php

/**
 * File containing the RequestListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST request listener.
 *
 * Flags a REST request as such using the is_rest_request attribute.
 */
class RequestListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $restPrefix;

    /**
     * @param string $restPrefix
     */
    public function __construct($restPrefix)
    {
        $this->restPrefix = $restPrefix;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // 10001 is to ensure that REST requests are tagged before CorsListener is called
            KernelEvents::REQUEST => array('onKernelRequest', 10001),
        );
    }

    /**
     * If the request is a REST one, sets the is_rest_request request attribute.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $isRestRequest = true;

        if (!$this->hasRestPrefix($event->getRequest())) {
            $isRestRequest = false;
        }

        $event->getRequest()->attributes->set('is_rest_request', $isRestRequest);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    protected function hasRestPrefix(Request $request)
    {
        return (
            strpos(
                $request->getPathInfo(),
                $this->restPrefix
            ) === 0
        );
    }
}
