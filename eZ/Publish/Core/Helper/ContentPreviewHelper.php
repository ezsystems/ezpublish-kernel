<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Helper;

use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContentPreviewHelper implements SiteAccessAware
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    protected $originalSiteAccess;

    public function __construct( EventDispatcherInterface $eventDispatcher )
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->originalSiteAccess = $siteAccess;
    }

    /**
     * Return original SiteAccess
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
}
