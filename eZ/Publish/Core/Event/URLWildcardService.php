<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\URLWildcardServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\Core\Event\URLWildcard\BeforeCreateEvent;
use eZ\Publish\Core\Event\URLWildcard\BeforeRemoveEvent;
use eZ\Publish\Core\Event\URLWildcard\BeforeTranslateEvent;
use eZ\Publish\Core\Event\URLWildcard\CreateEvent;
use eZ\Publish\Core\Event\URLWildcard\RemoveEvent;
use eZ\Publish\Core\Event\URLWildcard\TranslateEvent;

class URLWildcardService extends URLWildcardServiceDecorator
{
    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
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
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUrlWildcard();
        }

        $urlWildcard = $beforeEvent->hasUrlWildcard()
            ? $beforeEvent->getUrlWildcard()
            : parent::create($sourceUrl, $destinationUrl, $forward);

        $this->eventDispatcher->dispatch(new CreateEvent($urlWildcard, ...$eventData));

        return $urlWildcard;
    }

    public function remove(URLWildcard $urlWildcard): void
    {
        $eventData = [$urlWildcard];

        $beforeEvent = new BeforeRemoveEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::remove($urlWildcard);

        $this->eventDispatcher->dispatch(new RemoveEvent(...$eventData));
    }

    public function translate($url)
    {
        $eventData = [$url];

        $beforeEvent = new BeforeTranslateEvent(...$eventData);
        if ($this->eventDispatcher->dispatch($beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getResult();
        }

        $result = $beforeEvent->hasResult()
            ? $beforeEvent->getResult()
            : parent::translate($url);

        $this->eventDispatcher->dispatch(new TranslateEvent($result, ...$eventData));

        return $result;
    }
}
