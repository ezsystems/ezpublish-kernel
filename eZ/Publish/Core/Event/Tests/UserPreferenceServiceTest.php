<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Events\Tests;

use eZ\Publish\API\Repository\Events\UserPreference\BeforeSetUserPreferenceEvent as BeforeSetUserPreferenceEventInterface;
use eZ\Publish\API\Repository\Events\UserPreference\SetUserPreferenceEvent as SetUserPreferenceEventInterface;
use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\API\Repository\Events\UserPreferenceService;

class UserPreferenceServiceTest extends AbstractServiceTest
{
    public function testSetUserPreferenceEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetUserPreferenceEventInterface::class,
            SetUserPreferenceEventInterface::class
        );

        $parameters = [
            [],
        ];

        $innerServiceMock = $this->createMock(UserPreferenceServiceInterface::class);

        $service = new UserPreferenceService($innerServiceMock, $traceableEventDispatcher);
        $service->setUserPreference(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetUserPreferenceEventInterface::class, 0],
            [SetUserPreferenceEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetUserPreferenceStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetUserPreferenceEventInterface::class,
            SetUserPreferenceEventInterface::class
        );

        $parameters = [
            [],
        ];

        $innerServiceMock = $this->createMock(UserPreferenceServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeSetUserPreferenceEventInterface::class, function (BeforeSetUserPreferenceEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserPreferenceService($innerServiceMock, $traceableEventDispatcher);
        $service->setUserPreference(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetUserPreferenceEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeSetUserPreferenceEventInterface::class, 0],
            [SetUserPreferenceEventInterface::class, 0],
        ]);
    }
}
