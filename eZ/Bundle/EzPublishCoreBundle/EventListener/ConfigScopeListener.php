<?php

/**
 * File containing the ConfigScopeListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ViewProvider|\eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware
     */
    private $viewProviders;

    public function __construct(
        VersatileScopeInterface $configResolver,
        ViewManagerInterface $viewManager
    ) {
        $this->configResolver = $configResolver;
        $this->viewManager = $viewManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::CONFIG_SCOPE_CHANGE => array('onConfigScopeChange', 100),
            MVCEvents::CONFIG_SCOPE_RESTORE => array('onConfigScopeChange', 100),
        );
    }

    public function onConfigScopeChange(ScopeChangeEvent $event)
    {
        $siteAccess = $event->getSiteAccess();
        $this->configResolver->setDefaultScope($siteAccess->name);
        if ($this->viewManager instanceof SiteAccessAware) {
            $this->viewManager->setSiteAccess($siteAccess);
        }

        foreach ($this->viewProviders as $viewProvider) {
            if ($viewProvider instanceof SiteAccessAware) {
                $viewProvider->setSiteAccess($siteAccess);
            }
        }
    }

    /**
     * Sets the complete list of view providers.
     *
     * @param array $viewProviders
     */
    public function setViewProviders(array $viewProviders)
    {
        $this->viewProviders = $viewProviders;
    }
}
