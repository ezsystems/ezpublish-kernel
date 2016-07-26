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
     * {@inheritdoc}
     */
    public function purge($locationIds)
    {
        $this->purgeClient->purge((array)$locationIds);

        return $locationIds;
    }

    /**
     * {@inheritdoc}
     */
    public function purgeAll()
    {
        $this->purgeClient->purgeAll();
    }

    /**
     * {@inheritdoc}
     */
    public function purgeForContent($contentId, $locationIds = [])
    {
        $contentInfo = $this->contentService->loadContentInfo($contentId);

        // Can only gather relevant locations using ContentCacheClearEvent on published content
        if ($contentInfo->published) {
            $event = new ContentCacheClearEvent($contentInfo);
            $this->eventDispatcher->dispatch(MVCEvents::CACHE_CLEAR_CONTENT, $event);

            foreach ($event->getLocationsToClear() as $location) {
                $locationIds[] = $location->id;
            }
        }

        $this->purgeClient->purge(array_unique($locationIds));
    }
}
