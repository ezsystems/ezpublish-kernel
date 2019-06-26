<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContentPreviewHelper implements SiteAccessAware
{
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface */
    protected $siteAccessRouter;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    protected $originalSiteAccess;

    /** @var bool */
    private $previewActive = false;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $previewedContent;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $previewedLocation;

    public function __construct(EventDispatcherInterface $eventDispatcher, SiteAccessRouterInterface $siteAccessRouter)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->siteAccessRouter = $siteAccessRouter;
    }

    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->originalSiteAccess = $siteAccess;
    }

    /**
     * Return original SiteAccess.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    public function getOriginalSiteAccess()
    {
        return $this->originalSiteAccess;
    }

    /**
     * Switches configuration scope to $siteAccessName and returns the new SiteAccess to use for preview.
     *
     * @param string $siteAccessName
     *
     * @return SiteAccess
     */
    public function changeConfigScope($siteAccessName)
    {
        $event = new ScopeChangeEvent($this->siteAccessRouter->matchByName($siteAccessName));
        $this->eventDispatcher->dispatch(MVCEvents::CONFIG_SCOPE_CHANGE, $event);

        return $event->getSiteAccess();
    }

    /**
     * Restores original config scope.
     *
     * @return SiteAccess
     */
    public function restoreConfigScope()
    {
        $event = new ScopeChangeEvent($this->originalSiteAccess);
        $this->eventDispatcher->dispatch(MVCEvents::CONFIG_SCOPE_RESTORE, $event);

        return $event->getSiteAccess();
    }

    /**
     * @return bool
     */
    public function isPreviewActive()
    {
        return $this->previewActive;
    }

    /**
     * @param bool $previewActive
     */
    public function setPreviewActive($previewActive)
    {
        $this->previewActive = (bool)$previewActive;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function getPreviewedContent()
    {
        return $this->previewedContent;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $previewedContent
     */
    public function setPreviewedContent(Content $previewedContent)
    {
        $this->previewedContent = $previewedContent;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getPreviewedLocation()
    {
        return $this->previewedLocation;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Location $previewedLocation
     */
    public function setPreviewedLocation(Location $previewedLocation)
    {
        $this->previewedLocation = $previewedLocation;
    }
}
