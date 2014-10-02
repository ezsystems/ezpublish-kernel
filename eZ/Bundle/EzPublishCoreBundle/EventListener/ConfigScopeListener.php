<?php
/**
 * File containing the ConfigScopeListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigScopeListener extends ContainerAware implements EventSubscriberInterface
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
     * Array of serviceIds to reset in the container.
     *
     * @var array
     */
    private $resettableServiceIds;

    /**
     * Array of "fake" services handling dynamic settings injection.
     *
     * @var array
     */
    private $dynamicSettingsServiceIds;

    public function __construct(
        VersatileScopeInterface $configResolver,
        ViewManagerInterface $viewManager,
        array $resettableServiceIds,
        array $dynamicSettingsServiceIds
    )
    {
        $this->configResolver = $configResolver;
        $this->viewManager = $viewManager;
        $this->resettableServiceIds = $resettableServiceIds;
        $this->dynamicSettingsServiceIds = $dynamicSettingsServiceIds;
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

        // Ensure to reset services that need to be.
        foreach ( $this->resettableServiceIds as $serviceId )
        {
            $this->container->set( $serviceId, null );
        }

        // Force dynamic settings services to synchronize.
        // This will trigger services depending on dynamic settings to update if they use setter injection.
        foreach ( $this->dynamicSettingsServiceIds as $fakeServiceId )
        {
            $this->container->set( $fakeServiceId, null );
            $this->container->set( $fakeServiceId, $this->container->get( $fakeServiceId ) );
        }
    }
}
