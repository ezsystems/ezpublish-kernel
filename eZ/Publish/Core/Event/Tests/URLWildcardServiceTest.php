<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;
use eZ\Publish\Core\Event\URLWildcardService;
use eZ\Publish\Core\Event\URLWildcard\BeforeCreateEvent;
use eZ\Publish\Core\Event\URLWildcard\BeforeRemoveEvent;
use eZ\Publish\Core\Event\URLWildcard\BeforeTranslateEvent;
use eZ\Publish\Core\Event\URLWildcard\URLWildcardEvents;

class URLWildcardServiceTest extends AbstractServiceTest
{
    public function testRemoveEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_REMOVE,
            URLWildcardEvents::REMOVE
        );

        $parameters = [
            $this->createMock(URLWildcard::class),
        ];

        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $service->remove(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_REMOVE, 0],
            [URLWildcardEvents::REMOVE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_REMOVE,
            URLWildcardEvents::REMOVE
        );

        $parameters = [
            $this->createMock(URLWildcard::class),
        ];

        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);

        $traceableEventDispatcher->addListener(URLWildcardEvents::BEFORE_REMOVE, function (BeforeRemoveEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $service->remove(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_REMOVE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [URLWildcardEvents::REMOVE, 0],
            [URLWildcardEvents::BEFORE_REMOVE, 0],
        ]);
    }

    public function testCreateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_CREATE,
            URLWildcardEvents::CREATE
        );

        $parameters = [
            'random_value_5cff79c316c1f5.58580131',
            'random_value_5cff79c316c223.93334332',
            'random_value_5cff79c316c237.08397355',
        ];

        $urlWildcard = $this->createMock(URLWildcard::class);
        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);
        $innerServiceMock->method('create')->willReturn($urlWildcard);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->create(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($urlWildcard, $result);
        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_CREATE, 0],
            [URLWildcardEvents::CREATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_CREATE,
            URLWildcardEvents::CREATE
        );

        $parameters = [
            'random_value_5cff79c316c2d5.26653678',
            'random_value_5cff79c316c2e7.55400833',
            'random_value_5cff79c316c2f8.59874187',
        ];

        $urlWildcard = $this->createMock(URLWildcard::class);
        $eventUrlWildcard = $this->createMock(URLWildcard::class);
        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);
        $innerServiceMock->method('create')->willReturn($urlWildcard);

        $traceableEventDispatcher->addListener(URLWildcardEvents::BEFORE_CREATE, function (BeforeCreateEvent $event) use ($eventUrlWildcard) {
            $event->setUrlWildcard($eventUrlWildcard);
        }, 10);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->create(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUrlWildcard, $result);
        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_CREATE, 10],
            [URLWildcardEvents::BEFORE_CREATE, 0],
            [URLWildcardEvents::CREATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_CREATE,
            URLWildcardEvents::CREATE
        );

        $parameters = [
            'random_value_5cff79c316c359.46056769',
            'random_value_5cff79c316c361.53134429',
            'random_value_5cff79c316c374.82657815',
        ];

        $urlWildcard = $this->createMock(URLWildcard::class);
        $eventUrlWildcard = $this->createMock(URLWildcard::class);
        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);
        $innerServiceMock->method('create')->willReturn($urlWildcard);

        $traceableEventDispatcher->addListener(URLWildcardEvents::BEFORE_CREATE, function (BeforeCreateEvent $event) use ($eventUrlWildcard) {
            $event->setUrlWildcard($eventUrlWildcard);
            $event->stopPropagation();
        }, 10);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->create(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUrlWildcard, $result);
        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_CREATE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [URLWildcardEvents::CREATE, 0],
            [URLWildcardEvents::BEFORE_CREATE, 0],
        ]);
    }

    public function testTranslateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_TRANSLATE,
            URLWildcardEvents::TRANSLATE
        );

        $parameters = [
            'random_value_5cff79c316cfa7.72466150',
        ];

        $result = $this->createMock(URLWildcardTranslationResult::class);
        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);
        $innerServiceMock->method('translate')->willReturn($result);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->translate(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($result, $result);
        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_TRANSLATE, 0],
            [URLWildcardEvents::TRANSLATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnTranslateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_TRANSLATE,
            URLWildcardEvents::TRANSLATE
        );

        $parameters = [
            'random_value_5cff79c316d370.25863709',
        ];

        $result = $this->createMock(URLWildcardTranslationResult::class);
        $eventResult = $this->createMock(URLWildcardTranslationResult::class);
        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);
        $innerServiceMock->method('translate')->willReturn($result);

        $traceableEventDispatcher->addListener(URLWildcardEvents::BEFORE_TRANSLATE, function (BeforeTranslateEvent $event) use ($eventResult) {
            $event->setResult($eventResult);
        }, 10);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->translate(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventResult, $result);
        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_TRANSLATE, 10],
            [URLWildcardEvents::BEFORE_TRANSLATE, 0],
            [URLWildcardEvents::TRANSLATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testTranslateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            URLWildcardEvents::BEFORE_TRANSLATE,
            URLWildcardEvents::TRANSLATE
        );

        $parameters = [
            'random_value_5cff79c316d3f9.73226122',
        ];

        $result = $this->createMock(URLWildcardTranslationResult::class);
        $eventResult = $this->createMock(URLWildcardTranslationResult::class);
        $innerServiceMock = $this->createMock(URLWildcardServiceInterface::class);
        $innerServiceMock->method('translate')->willReturn($result);

        $traceableEventDispatcher->addListener(URLWildcardEvents::BEFORE_TRANSLATE, function (BeforeTranslateEvent $event) use ($eventResult) {
            $event->setResult($eventResult);
            $event->stopPropagation();
        }, 10);

        $service = new URLWildcardService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->translate(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventResult, $result);
        $this->assertSame($calledListeners, [
            [URLWildcardEvents::BEFORE_TRANSLATE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [URLWildcardEvents::TRANSLATE, 0],
            [URLWildcardEvents::BEFORE_TRANSLATE, 0],
        ]);
    }
}
