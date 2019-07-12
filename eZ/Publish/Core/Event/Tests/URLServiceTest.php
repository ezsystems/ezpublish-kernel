<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\URL\BeforeUpdateUrlEvent as BeforeUpdateUrlEventInterface;
use eZ\Publish\API\Repository\Events\URL\UpdateUrlEvent as UpdateUrlEventInterface;
use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\Core\Event\URLService;

class URLServiceTest extends AbstractServiceTest
{
    public function testUpdateUrlEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUrlEventInterface::class,
            UpdateUrlEventInterface::class
        );

        $parameters = [
            $this->createMock(URL::class),
            $this->createMock(URLUpdateStruct::class),
        ];

        $updatedUrl = $this->createMock(URL::class);
        $innerServiceMock = $this->createMock(URLServiceInterface::class);
        $innerServiceMock->method('updateUrl')->willReturn($updatedUrl);

        $service = new URLService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUrl(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedUrl, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUrlEventInterface::class, 0],
            [UpdateUrlEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUrlResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUrlEventInterface::class,
            UpdateUrlEventInterface::class
        );

        $parameters = [
            $this->createMock(URL::class),
            $this->createMock(URLUpdateStruct::class),
        ];

        $updatedUrl = $this->createMock(URL::class);
        $eventUpdatedUrl = $this->createMock(URL::class);
        $innerServiceMock = $this->createMock(URLServiceInterface::class);
        $innerServiceMock->method('updateUrl')->willReturn($updatedUrl);

        $traceableEventDispatcher->addListener(BeforeUpdateUrlEventInterface::class, function (BeforeUpdateUrlEventInterface $event) use ($eventUpdatedUrl) {
            $event->setUpdatedUrl($eventUpdatedUrl);
        }, 10);

        $service = new URLService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUrl(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUrl, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUrlEventInterface::class, 10],
            [BeforeUpdateUrlEventInterface::class, 0],
            [UpdateUrlEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUrlStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUrlEventInterface::class,
            UpdateUrlEventInterface::class
        );

        $parameters = [
            $this->createMock(URL::class),
            $this->createMock(URLUpdateStruct::class),
        ];

        $updatedUrl = $this->createMock(URL::class);
        $eventUpdatedUrl = $this->createMock(URL::class);
        $innerServiceMock = $this->createMock(URLServiceInterface::class);
        $innerServiceMock->method('updateUrl')->willReturn($updatedUrl);

        $traceableEventDispatcher->addListener(BeforeUpdateUrlEventInterface::class, function (BeforeUpdateUrlEventInterface $event) use ($eventUpdatedUrl) {
            $event->setUpdatedUrl($eventUpdatedUrl);
            $event->stopPropagation();
        }, 10);

        $service = new URLService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUrl(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUrl, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUrlEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateUrlEventInterface::class, 0],
            [UpdateUrlEventInterface::class, 0],
        ]);
    }
}
