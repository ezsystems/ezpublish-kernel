<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\EventListener;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds content's assigned locations to the cache-clear list.
 */
class AssignedLocationsListener implements EventSubscriberInterface
{
    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public static function getSubscribedEvents()
    {
        return [MVCEvents::CACHE_CLEAR_CONTENT => ['onContentCacheClear', 100]];
    }

    public function onContentCacheClear(ContentCacheClearEvent $event)
    {
        $contentInfo = $event->getContentInfo();
        foreach ($this->locationService->loadLocations($contentInfo) as $location) {
            $event->addLocationToClear($location);
        }
    }
}
