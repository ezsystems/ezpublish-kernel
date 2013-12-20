<?php
/**
 * File containing the ConfigScopeListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigScopeListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface|\eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware
     */
    private $viewManager;

    public function __construct( VersatileScopeInterface $configResolver, ViewManagerInterface $viewManager )
    {
        $this->configResolver = $configResolver;
        $this->viewManager = $viewManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::CONFIG_SCOPE_CHANGE => 'onConfigScopeChange',
            MVCEvents::CONFIG_SCOPE_RESTORE => 'onConfigScopeChange',
        );
    }

    public function onConfigScopeChange( ScopeChangeEvent $event )
    {
        $siteAccess = $event->getSiteAccess();
        $this->configResolver->setDefaultScope( $siteAccess->name );
        if ( $this->viewManager instanceof SiteAccessAware )
        {
            $this->viewManager->setSiteAccess( $siteAccess );
        }
    }
}
