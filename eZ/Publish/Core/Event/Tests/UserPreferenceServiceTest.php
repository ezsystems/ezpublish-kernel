<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\Core\Event\UserPreferenceService;
use eZ\Publish\Core\Event\UserPreference\BeforeSetUserPreferenceEvent;
use eZ\Publish\Core\Event\UserPreference\UserPreferenceEvents;

class UserPreferenceServiceTest extends AbstractServiceTest
{
    public function testSetUserPreferenceEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserPreferenceEvents::BEFORE_SET_USER_PREFERENCE,
            UserPreferenceEvents::SET_USER_PREFERENCE
        );

        $parameters = [
            [],
        ];

        $innerServiceMock = $this->createMock(UserPreferenceServiceInterface::class);

        $service = new UserPreferenceService($innerServiceMock, $traceableEventDispatcher);
        $service->setUserPreference(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [UserPreferenceEvents::BEFORE_SET_USER_PREFERENCE, 0],
            [UserPreferenceEvents::SET_USER_PREFERENCE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetUserPreferenceStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserPreferenceEvents::BEFORE_SET_USER_PREFERENCE,
            UserPreferenceEvents::SET_USER_PREFERENCE
        );

        $parameters = [
            [],
        ];

        $innerServiceMock = $this->createMock(UserPreferenceServiceInterface::class);

        $traceableEventDispatcher->addListener(UserPreferenceEvents::BEFORE_SET_USER_PREFERENCE, function (BeforeSetUserPreferenceEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserPreferenceService($innerServiceMock, $traceableEventDispatcher);
        $service->setUserPreference(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [UserPreferenceEvents::BEFORE_SET_USER_PREFERENCE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserPreferenceEvents::SET_USER_PREFERENCE, 0],
            [UserPreferenceEvents::BEFORE_SET_USER_PREFERENCE, 0],
        ]);
    }
}
