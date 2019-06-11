<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\UserPreferenceServiceDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\Core\Event\UserPreference\BeforeSetUserPreferenceEvent;
use eZ\Publish\Core\Event\UserPreference\SetUserPreferenceEvent;
use eZ\Publish\Core\Event\UserPreference\UserPreferenceEvents;

class UserPreferenceService extends UserPreferenceServiceDecorator
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        UserPreferenceServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function setUserPreference(array $userPreferenceSetStructs): void
    {
        $eventData = [$userPreferenceSetStructs];

        $beforeEvent = new BeforeSetUserPreferenceEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(UserPreferenceEvents::BEFORE_SET_USER_PREFERENCE, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::setUserPreference($userPreferenceSetStructs);

        $this->eventDispatcher->dispatch(
            UserPreferenceEvents::SET_USER_PREFERENCE,
            new SetUserPreferenceEvent(...$eventData)
        );
    }
}
