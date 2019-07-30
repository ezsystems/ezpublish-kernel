<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\Events\URLAlias\BeforeCreateGlobalUrlAliasEvent as BeforeCreateGlobalUrlAliasEventInterface;
use eZ\Publish\API\Repository\Events\URLAlias\BeforeCreateUrlAliasEvent as BeforeCreateUrlAliasEventInterface;
use eZ\Publish\API\Repository\Events\URLAlias\BeforeRefreshSystemUrlAliasesForLocationEvent as BeforeRefreshSystemUrlAliasesForLocationEventInterface;
use eZ\Publish\API\Repository\Events\URLAlias\BeforeRemoveAliasesEvent as BeforeRemoveAliasesEventInterface;
use eZ\Publish\API\Repository\Events\URLAlias\CreateGlobalUrlAliasEvent as CreateGlobalUrlAliasEventInterface;
use eZ\Publish\API\Repository\Events\URLAlias\CreateUrlAliasEvent as CreateUrlAliasEventInterface;
use eZ\Publish\API\Repository\Events\URLAlias\RefreshSystemUrlAliasesForLocationEvent as RefreshSystemUrlAliasesForLocationEventInterface;
use eZ\Publish\API\Repository\Events\URLAlias\RemoveAliasesEvent as RemoveAliasesEventInterface;
use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Events\URLAlias\BeforeCreateGlobalUrlAliasEvent;
use eZ\Publish\API\Repository\Events\URLAlias\BeforeCreateUrlAliasEvent;
use eZ\Publish\API\Repository\Events\URLAlias\BeforeRefreshSystemUrlAliasesForLocationEvent;
use eZ\Publish\API\Repository\Events\URLAlias\BeforeRemoveAliasesEvent;
use eZ\Publish\API\Repository\Events\URLAlias\CreateGlobalUrlAliasEvent;
use eZ\Publish\API\Repository\Events\URLAlias\CreateUrlAliasEvent;
use eZ\Publish\API\Repository\Events\URLAlias\RefreshSystemUrlAliasesForLocationEvent;
use eZ\Publish\API\Repository\Events\URLAlias\RemoveAliasesEvent;
use eZ\Publish\SPI\Repository\Decorator\URLAliasServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class URLAliasService extends URLAliasServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        URLAliasServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createUrlAlias(
        Location $location,
        $path,
        $languageCode,
        $forwarding = false,
        $alwaysAvailable = false
    ) {
        $eventData = [
            $location,
            $path,
            $languageCode,
            $forwarding,
            $alwaysAvailable,
        ];

        $beforeEvent = new BeforeCreateUrlAliasEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateUrlAliasEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUrlAlias();
        }

        $urlAlias = $beforeEvent->hasUrlAlias()
            ? $beforeEvent->getUrlAlias()
            : $this->innerService->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);

        $this->eventDispatcher->dispatch(
            new CreateUrlAliasEvent($urlAlias, ...$eventData),
            CreateUrlAliasEventInterface::class
        );

        return $urlAlias;
    }

    public function createGlobalUrlAlias(
        $resource,
        $path,
        $languageCode,
        $forwarding = false,
        $alwaysAvailable = false
    ) {
        $eventData = [
            $resource,
            $path,
            $languageCode,
            $forwarding,
            $alwaysAvailable,
        ];

        $beforeEvent = new BeforeCreateGlobalUrlAliasEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateGlobalUrlAliasEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUrlAlias();
        }

        $urlAlias = $beforeEvent->hasUrlAlias()
            ? $beforeEvent->getUrlAlias()
            : $this->innerService->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);

        $this->eventDispatcher->dispatch(
            new CreateGlobalUrlAliasEvent($urlAlias, ...$eventData),
            CreateGlobalUrlAliasEventInterface::class
        );

        return $urlAlias;
    }

    public function removeAliases(array $aliasList): void
    {
        $eventData = [$aliasList];

        $beforeEvent = new BeforeRemoveAliasesEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRemoveAliasesEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->removeAliases($aliasList);

        $this->eventDispatcher->dispatch(
            new RemoveAliasesEvent(...$eventData),
            RemoveAliasesEventInterface::class
        );
    }

    public function refreshSystemUrlAliasesForLocation(Location $location): void
    {
        $eventData = [$location];

        $beforeEvent = new BeforeRefreshSystemUrlAliasesForLocationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRefreshSystemUrlAliasesForLocationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->refreshSystemUrlAliasesForLocation($location);

        $this->eventDispatcher->dispatch(
            new RefreshSystemUrlAliasesForLocationEvent(...$eventData),
            RefreshSystemUrlAliasesForLocationEventInterface::class
        );
    }
}
