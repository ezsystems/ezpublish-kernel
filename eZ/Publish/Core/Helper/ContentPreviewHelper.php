<?php
/**
 * File containing the ContentPreviewHelper class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContentPreviewHelper implements SiteAccessAware
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    protected $originalSiteAccess;

    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->originalSiteAccess = $siteAccess;
    }

    /**
     * Switches configuration scope to $siteAccessName and returns the new SiteAccess to use for preview.
     *
     * @param string $siteAccessName
     *
     * @return SiteAccess
     */
    public function changeConfigScope( $siteAccessName )
    {
        $event = new ScopeChangeEvent( new SiteAccess( $siteAccessName, 'preview' ) );
        $this->eventDispatcher->dispatch( MVCEvents::CONFIG_SCOPE_CHANGE, $event );

        return $event->getSiteAccess();
    }

    /**
     * Restores original config scope.
     *
     * @return SiteAccess
     */
    public function restoreConfigScope()
    {
        $event = new ScopeChangeEvent( $this->originalSiteAccess );
        $this->eventDispatcher->dispatch( MVCEvents::CONFIG_SCOPE_RESTORE, $event );

        return $event->getSiteAccess();
    }

    /**
     * Returns a valid Location object for $contentId.
     * Will either load mainLocationId (if available) or build a virtual Location object.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location|Location
     */
    public function getPreviewLocation( $contentId )
    {
        // contentInfo must be reloaded as content is not published yet (e.g. no mainLocationId)
        $contentInfo = $this->contentService->loadContentInfo( $contentId );
        // mainLocationId already exists, content has been published at least once.
        if ( $contentInfo->mainLocationId )
        {
            $location = $this->locationService->loadLocation( $contentInfo->mainLocationId );
        }
        // New Content, never published, create a virtual location object.
        else
        {
            $location = new Location( array( 'contentInfo' => $contentInfo ) );
        }

        return $location;
    }
}
