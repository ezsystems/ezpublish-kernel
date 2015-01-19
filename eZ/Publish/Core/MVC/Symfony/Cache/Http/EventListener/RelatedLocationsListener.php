<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\EventListener;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds locations from related content to the Http cache clear list, for given content.
 * Both relation and reverse relation are taken into account.
 */
class RelatedLocationsListener implements EventSubscriberInterface
{
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct( ContentService $contentService, LocationService $locationService )
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }

    public static function getSubscribedEvents()
    {
        return [MVCEvents::CACHE_CLEAR_CONTENT => ['onContentCacheClear', 100]];
    }

    public function onContentCacheClear( ContentCacheClearEvent $event )
    {
        $contentInfo = $event->getContentInfo();
        $versionInfo = $this->contentService->loadVersionInfo( $contentInfo );

        foreach ( $this->contentService->loadRelations( $versionInfo ) as $relation )
        {
            foreach ( $this->locationService->loadLocations( $relation->getDestinationContentInfo() ) as $relatedLocation )
            {
                $event->addLocationToClear( $relatedLocation );
            }
        }

        foreach ( $this->contentService->loadReverseRelations( $contentInfo ) as $reverseRelation )
        {
            foreach ( $this->locationService->loadLocations( $reverseRelation->getSourceContentInfo() ) as $relatedLocation )
            {
                $event->addLocationToClear( $relatedLocation );
            }
        }
    }
}
