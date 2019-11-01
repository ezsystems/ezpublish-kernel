<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLUpdateStruct;
use eZ\Publish\API\Repository\Events\URL\BeforeUpdateUrlEvent;
use eZ\Publish\API\Repository\Events\URL\UpdateUrlEvent;
use eZ\Publish\SPI\Repository\Decorator\URLServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class URLService extends URLServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        URLServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function updateUrl(
        URL $url,
        URLUpdateStruct $struct
    ): URL {
        $eventData = [
            $url,
            $struct,
        ];

        $beforeEvent = new BeforeUpdateUrlEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedUrl();
        }

        $updatedUrl = $beforeEvent->hasUpdatedUrl()
            ? $beforeEvent->getUpdatedUrl()
            : $this->innerService->updateUrl($url, $struct);

        $this->eventDispatcher->dispatch(
            new UpdateUrlEvent($updatedUrl, ...$eventData)
        );

        return $updatedUrl;
    }
}
