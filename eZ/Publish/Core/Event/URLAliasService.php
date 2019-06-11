<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\URLAliasServiceDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\URLAlias\BeforeCreateGlobalUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\BeforeCreateUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\BeforeRefreshSystemUrlAliasesForLocationEvent;
use eZ\Publish\Core\Event\URLAlias\BeforeRemoveAliasesEvent;
use eZ\Publish\Core\Event\URLAlias\CreateGlobalUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\CreateUrlAliasEvent;
use eZ\Publish\Core\Event\URLAlias\RefreshSystemUrlAliasesForLocationEvent;
use eZ\Publish\Core\Event\URLAlias\RemoveAliasesEvent;
use eZ\Publish\Core\Event\URLAlias\URLAliasEvents;

class URLAliasService extends URLAliasServiceDecorator
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
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
        if ($this->eventDispatcher->dispatch(URLAliasEvents::BEFORE_CREATE_URL_ALIAS, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUrlAlias();
        }

        $urlAlias = $beforeEvent->hasUrlAlias()
            ? $beforeEvent->getUrlAlias()
            : parent::createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);

        $this->eventDispatcher->dispatch(
            URLAliasEvents::CREATE_URL_ALIAS,
            new CreateUrlAliasEvent($urlAlias, ...$eventData)
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
        if ($this->eventDispatcher->dispatch(URLAliasEvents::BEFORE_CREATE_GLOBAL_URL_ALIAS, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUrlAlias();
        }

        $urlAlias = $beforeEvent->hasUrlAlias()
            ? $beforeEvent->getUrlAlias()
            : parent::createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);

        $this->eventDispatcher->dispatch(
            URLAliasEvents::CREATE_GLOBAL_URL_ALIAS,
            new CreateGlobalUrlAliasEvent($urlAlias, ...$eventData)
        );

        return $urlAlias;
    }

    public function removeAliases(array $aliasList): void
    {
        $eventData = [$aliasList];

        $beforeEvent = new BeforeRemoveAliasesEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(URLAliasEvents::BEFORE_REMOVE_ALIASES, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::removeAliases($aliasList);

        $this->eventDispatcher->dispatch(
            URLAliasEvents::REMOVE_ALIASES,
            new RemoveAliasesEvent(...$eventData)
        );
    }

    public function refreshSystemUrlAliasesForLocation(Location $location): void
    {
        $eventData = [$location];

        $beforeEvent = new BeforeRefreshSystemUrlAliasesForLocationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(URLAliasEvents::BEFORE_REFRESH_SYSTEM_URL_ALIASES_FOR_LOCATION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::refreshSystemUrlAliasesForLocation($location);

        $this->eventDispatcher->dispatch(
            URLAliasEvents::REFRESH_SYSTEM_URL_ALIASES_FOR_LOCATION,
            new RefreshSystemUrlAliasesForLocationEvent(...$eventData)
        );
    }
}
