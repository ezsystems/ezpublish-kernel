<?php

/**
 * File containing the InstantCachePurger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InstantCachePurger implements GatewayCachePurger
{
    /**
     * @var PurgeClientInterface
     */
    protected $purgeClient;

    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        PurgeClientInterface $purgeClient,
        ContentService $contentService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->purgeClient = $purgeClient;
        $this->contentService = $contentService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @deprecated as of 6.0. Will be removed in 7.0. Use purgeForContent() instead.
     *
     * @param mixed $cacheElements
     *
     * @return mixed
     */
    public function purge($cacheElements)
    {
        $this->purgeClient->purge((array)$cacheElements);

        return $cacheElements;
    }

    public function purgeAll()
    {
        $this->purgeClient->purgeAll();
    }

    /**
     * Purge Content cache using location id's returned from ContentCacheClearEvent.
     *
     * @deprecated in 6.5, design flaw on deleted/trashed content, use purge() on cases you are affected by this for now.
     *
     * @param mixed $contentId
     * @param array $locationIds Initial location id's from signal to take into account.
     */
    public function purgeForContent($contentId, $locationIds = [])
    {
        $contentInfo = $this->contentService->loadContentInfo($contentId);
        $event = new ContentCacheClearEvent($contentInfo);
        $this->eventDispatcher->dispatch(MVCEvents::CACHE_CLEAR_CONTENT, $event);

        foreach ($event->getLocationsToClear() as $location) {
            $locationIds[] = $location->id;
        }

        $this->purgeClient->purge(array_unique($locationIds));
    }
}
