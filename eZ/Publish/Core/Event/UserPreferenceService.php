<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\Events\UserPreference\BeforeSetUserPreferenceEvent as BeforeSetUserPreferenceEventInterface;
use eZ\Publish\API\Repository\Events\UserPreference\SetUserPreferenceEvent as SetUserPreferenceEventInterface;
use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\API\Repository\Events\UserPreference\BeforeSetUserPreferenceEvent;
use eZ\Publish\API\Repository\Events\UserPreference\SetUserPreferenceEvent;
use eZ\Publish\SPI\Repository\Decorator\UserPreferenceServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserPreferenceService extends UserPreferenceServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeSetUserPreferenceEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->setUserPreference($userPreferenceSetStructs);

        $this->eventDispatcher->dispatch(
            new SetUserPreferenceEvent(...$eventData),
            SetUserPreferenceEventInterface::class
        );
    }
}
