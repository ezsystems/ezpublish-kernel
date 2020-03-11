<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

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
use eZ\Publish\API\Repository\Values\Content\URLAlias;
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
        string $path,
        string $languageCode,
        bool $forwarding = false,
        bool $alwaysAvailable = false
    ): URLAlias {
        $eventData = [
            $location,
            $path,
            $languageCode,
            $forwarding,
            $alwaysAvailable,
        ];

        $beforeEvent = new BeforeCreateUrlAliasEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUrlAlias();
        }

        $urlAlias = $beforeEvent->hasUrlAlias()
            ? $beforeEvent->getUrlAlias()
            : $this->innerService->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);

        $this->eventDispatcher->dispatch(
            new CreateUrlAliasEvent($urlAlias, ...$eventData)
        );

        return $urlAlias;
    }

    public function createGlobalUrlAlias(
        string $resource,
        string $path,
        string $languageCode,
        bool $forwarding = false,
        bool $alwaysAvailable = false
    ): URLAlias {
        $eventData = [
            $resource,
            $path,
            $languageCode,
            $forwarding,
            $alwaysAvailable,
        ];

        $beforeEvent = new BeforeCreateGlobalUrlAliasEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUrlAlias();
        }

        $urlAlias = $beforeEvent->hasUrlAlias()
            ? $beforeEvent->getUrlAlias()
            : $this->innerService->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);

        $this->eventDispatcher->dispatch(
            new CreateGlobalUrlAliasEvent($urlAlias, ...$eventData)
        );

        return $urlAlias;
    }

    public function removeAliases(array $aliasList): void
    {
        $eventData = [$aliasList];

        $beforeEvent = new BeforeRemoveAliasesEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->removeAliases($aliasList);

        $this->eventDispatcher->dispatch(
            new RemoveAliasesEvent(...$eventData)
        );
    }

    public function refreshSystemUrlAliasesForLocation(Location $location): void
    {
        $eventData = [$location];

        $beforeEvent = new BeforeRefreshSystemUrlAliasesForLocationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->refreshSystemUrlAliasesForLocation($location);

        $this->eventDispatcher->dispatch(
            new RefreshSystemUrlAliasesForLocationEvent(...$eventData)
        );
    }
}
