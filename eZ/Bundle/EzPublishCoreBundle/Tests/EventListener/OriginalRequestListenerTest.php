<?php

/**
 * File containing the OriginalRequestListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\OriginalRequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class OriginalRequestListenerTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::REQUEST => ['onKernelRequest', 200],
            ],
            OriginalRequestListener::getSubscribedEvents()
        );
    }

    public function testOnKernelRequestNotMaster()
    {
        $request = new Request();
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        $listener = new OriginalRequestListener();
        $listener->onKernelRequest($event);
        $this->assertFalse($request->attributes->has('_ez_original_request'));
    }

    public function testOnKernelRequestNoOriginalRequest()
    {
        $request = new Request();
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $listener = new OriginalRequestListener();
        $listener->onKernelRequest($event);
        $this->assertFalse($request->attributes->has('_ez_original_request'));
    }

    public function testOnKernelRequestWithOriginalRequest()
    {
        $scheme = 'http';
        $host = 'phoenix-rises.fm';
        $port = 1234;
        $originalUri = '/foo/bar';
        $originalAccept = 'blabla';

        $expectedOriginalRequest = Request::create(sprintf('%s://%s:%d%s', $scheme, $host, $port, $originalUri));
        $expectedOriginalRequest->headers->set('accept', $originalAccept);
        $expectedOriginalRequest->server->set('HTTP_ACCEPT', $originalAccept);

        $request = Request::create(sprintf('%s://%s:%d', $scheme, $host, $port) . '/_fos_user_hash');
        $request->headers->set('x-fos-original-url', $originalUri);
        $request->headers->set('x-fos-original-accept', $originalAccept);
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $listener = new OriginalRequestListener();
        $listener->onKernelRequest($event);
        $this->assertEquals($expectedOriginalRequest, $request->attributes->get('_ez_original_request'));
    }
}
