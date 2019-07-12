<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\Events\URLWildcard\BeforeCreateEvent as BeforeCreateEventInterface;
use eZ\Publish\API\Repository\Events\URLWildcard\BeforeRemoveEvent as BeforeRemoveEventInterface;
use eZ\Publish\API\Repository\Events\URLWildcard\BeforeTranslateEvent as BeforeTranslateEventInterface;
use eZ\Publish\API\Repository\Events\URLWildcard\CreateEvent as CreateEventInterface;
use eZ\Publish\API\Repository\Events\URLWildcard\RemoveEvent as RemoveEventInterface;
use eZ\Publish\API\Repository\Events\URLWildcard\TranslateEvent as TranslateEventInterface;
use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\Core\Event\URLWildcard\BeforeCreateEvent;
use eZ\Publish\Core\Event\URLWildcard\BeforeRemoveEvent;
use eZ\Publish\Core\Event\URLWildcard\BeforeTranslateEvent;
use eZ\Publish\Core\Event\URLWildcard\CreateEvent;
use eZ\Publish\Core\Event\URLWildcard\RemoveEvent;
use eZ\Publish\Core\Event\URLWildcard\TranslateEvent;
use eZ\Publish\SPI\Repository\Decorator\URLWildcardServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class URLWildcardService extends URLWildcardServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        URLWildcardServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(
        $sourceUrl,
        $destinationUrl,
        $forward = false
    ) {
        $eventData = [
            $sourceUrl,
            $destinationUrl,
            $forward,
        ];

        $beforeEvent = new BeforeCreateEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUrlWildcard();
        }

        $urlWildcard = $beforeEvent->hasUrlWildcard()
            ? $beforeEvent->getUrlWildcard()
            : $this->innerService->create($sourceUrl, $destinationUrl, $forward);

        $this->eventDispatcher->dispatch(
            new CreateEvent($urlWildcard, ...$eventData),
            CreateEventInterface::class
        );

        return $urlWildcard;
    }

    public function remove(URLWildcard $urlWildcard): void
    {
        $eventData = [$urlWildcard];

        $beforeEvent = new BeforeRemoveEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRemoveEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->remove($urlWildcard);

        $this->eventDispatcher->dispatch(
            new RemoveEvent(...$eventData),
            RemoveEventInterface::class
        );
    }

    public function translate($url)
    {
        $eventData = [$url];

        $beforeEvent = new BeforeTranslateEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeTranslateEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getResult();
        }

        $result = $beforeEvent->hasResult()
            ? $beforeEvent->getResult()
            : $this->innerService->translate($url);

        $this->eventDispatcher->dispatch(
            new TranslateEvent($result, ...$eventData),
            TranslateEventInterface::class
        );

        return $result;
    }
}
