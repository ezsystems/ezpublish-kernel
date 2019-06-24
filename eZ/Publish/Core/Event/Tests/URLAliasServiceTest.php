<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\Event\URLAliasService;
use eZ\Publish\Core\Event\URLAlias\CreateGlobalUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\BeforeCreateGlobalUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\CreateUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\BeforeCreateUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\RefreshSystemUrlAliasesForLocationEvent;
use eZ\Publish\Core\Event\URLAlias\BeforeRefreshSystemUrlAliasesForLocationEvent;
use eZ\Publish\Core\Event\URLAlias\RemoveAliasesEvent;
use eZ\Publish\Core\Event\URLAlias\BeforeRemoveAliasesEvent;

class URLAliasServiceTest extends AbstractServiceTest
{
    public function testCreateGlobalUrlAliasEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateGlobalUrlAliasEvent::class,
            CreateGlobalUrlAliasEvent::class
        );

        $parameters = [
            'random_value_5cff79c3183471.48198669',
            'random_value_5cff79c3183491.90712521',
            'random_value_5cff79c31834a2.27245619',
            'random_value_5cff79c31834b7.17763784',
            'random_value_5cff79c31834c3.69513526',
        ];

        $urlAlias = $this->createMock(URLAlias::class);
        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);
        $innerServiceMock->method('createGlobalUrlAlias')->willReturn($urlAlias);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createGlobalUrlAlias(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($urlAlias, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateGlobalUrlAliasEvent::class, 0],
            [CreateGlobalUrlAliasEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateGlobalUrlAliasResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateGlobalUrlAliasEvent::class,
            CreateGlobalUrlAliasEvent::class
        );

        $parameters = [
            'random_value_5cff79c3183999.45723962',
            'random_value_5cff79c31839a0.16919746',
            'random_value_5cff79c31839b6.04657069',
            'random_value_5cff79c31839c8.99027893',
            'random_value_5cff79c31839d9.22502123',
        ];

        $urlAlias = $this->createMock(URLAlias::class);
        $eventUrlAlias = $this->createMock(URLAlias::class);
        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);
        $innerServiceMock->method('createGlobalUrlAlias')->willReturn($urlAlias);

        $traceableEventDispatcher->addListener(BeforeCreateGlobalUrlAliasEvent::class, function (BeforeCreateGlobalUrlAliasEvent $event) use ($eventUrlAlias) {
            $event->setUrlAlias($eventUrlAlias);
        }, 10);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createGlobalUrlAlias(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUrlAlias, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateGlobalUrlAliasEvent::class, 10],
            [BeforeCreateGlobalUrlAliasEvent::class, 0],
            [CreateGlobalUrlAliasEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateGlobalUrlAliasStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateGlobalUrlAliasEvent::class,
            CreateGlobalUrlAliasEvent::class
        );

        $parameters = [
            'random_value_5cff79c3183a40.78467503',
            'random_value_5cff79c3183a52.60688594',
            'random_value_5cff79c3183a62.37338343',
            'random_value_5cff79c3183a74.31062414',
            'random_value_5cff79c3183a85.16422549',
        ];

        $urlAlias = $this->createMock(URLAlias::class);
        $eventUrlAlias = $this->createMock(URLAlias::class);
        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);
        $innerServiceMock->method('createGlobalUrlAlias')->willReturn($urlAlias);

        $traceableEventDispatcher->addListener(BeforeCreateGlobalUrlAliasEvent::class, function (BeforeCreateGlobalUrlAliasEvent $event) use ($eventUrlAlias) {
            $event->setUrlAlias($eventUrlAlias);
            $event->stopPropagation();
        }, 10);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createGlobalUrlAlias(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUrlAlias, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateGlobalUrlAliasEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateGlobalUrlAliasEvent::class, 0],
            [CreateGlobalUrlAliasEvent::class, 0],
        ]);
    }

    public function testRefreshSystemUrlAliasesForLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRefreshSystemUrlAliasesForLocationEvent::class,
            RefreshSystemUrlAliasesForLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $service->refreshSystemUrlAliasesForLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRefreshSystemUrlAliasesForLocationEvent::class, 0],
            [RefreshSystemUrlAliasesForLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRefreshSystemUrlAliasesForLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRefreshSystemUrlAliasesForLocationEvent::class,
            RefreshSystemUrlAliasesForLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeRefreshSystemUrlAliasesForLocationEvent::class, function (BeforeRefreshSystemUrlAliasesForLocationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $service->refreshSystemUrlAliasesForLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRefreshSystemUrlAliasesForLocationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRefreshSystemUrlAliasesForLocationEvent::class, 0],
            [RefreshSystemUrlAliasesForLocationEvent::class, 0],
        ]);
    }

    public function testCreateUrlAliasEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUrlAliasEvent::class,
            CreateUrlAliasEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            'random_value_5cff79c3184f05.03459159',
            'random_value_5cff79c3184f14.18292216',
            'random_value_5cff79c3184f24.01158164',
            'random_value_5cff79c3184f32.03833593',
        ];

        $urlAlias = $this->createMock(URLAlias::class);
        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);
        $innerServiceMock->method('createUrlAlias')->willReturn($urlAlias);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUrlAlias(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($urlAlias, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUrlAliasEvent::class, 0],
            [CreateUrlAliasEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateUrlAliasResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUrlAliasEvent::class,
            CreateUrlAliasEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            'random_value_5cff79c3184fd7.07408772',
            'random_value_5cff79c3184fe2.98616568',
            'random_value_5cff79c3184ff0.62652505',
            'random_value_5cff79c3185003.87499400',
        ];

        $urlAlias = $this->createMock(URLAlias::class);
        $eventUrlAlias = $this->createMock(URLAlias::class);
        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);
        $innerServiceMock->method('createUrlAlias')->willReturn($urlAlias);

        $traceableEventDispatcher->addListener(BeforeCreateUrlAliasEvent::class, function (BeforeCreateUrlAliasEvent $event) use ($eventUrlAlias) {
            $event->setUrlAlias($eventUrlAlias);
        }, 10);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUrlAlias(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUrlAlias, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUrlAliasEvent::class, 10],
            [BeforeCreateUrlAliasEvent::class, 0],
            [CreateUrlAliasEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateUrlAliasStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUrlAliasEvent::class,
            CreateUrlAliasEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            'random_value_5cff79c3185072.24449261',
            'random_value_5cff79c3185080.62311461',
            'random_value_5cff79c3185095.31877612',
            'random_value_5cff79c31850a4.20254218',
        ];

        $urlAlias = $this->createMock(URLAlias::class);
        $eventUrlAlias = $this->createMock(URLAlias::class);
        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);
        $innerServiceMock->method('createUrlAlias')->willReturn($urlAlias);

        $traceableEventDispatcher->addListener(BeforeCreateUrlAliasEvent::class, function (BeforeCreateUrlAliasEvent $event) use ($eventUrlAlias) {
            $event->setUrlAlias($eventUrlAlias);
            $event->stopPropagation();
        }, 10);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUrlAlias(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUrlAlias, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUrlAliasEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateUrlAliasEvent::class, 0],
            [CreateUrlAliasEvent::class, 0],
        ]);
    }

    public function testRemoveAliasesEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveAliasesEvent::class,
            RemoveAliasesEvent::class
        );

        $parameters = [
            [],
        ];

        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $service->removeAliases(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRemoveAliasesEvent::class, 0],
            [RemoveAliasesEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveAliasesStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveAliasesEvent::class,
            RemoveAliasesEvent::class
        );

        $parameters = [
            [],
        ];

        $innerServiceMock = $this->createMock(URLAliasServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeRemoveAliasesEvent::class, function (BeforeRemoveAliasesEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new URLAliasService($innerServiceMock, $traceableEventDispatcher);
        $service->removeAliases(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRemoveAliasesEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRemoveAliasesEvent::class, 0],
            [RemoveAliasesEvent::class, 0],
        ]);
    }
}
