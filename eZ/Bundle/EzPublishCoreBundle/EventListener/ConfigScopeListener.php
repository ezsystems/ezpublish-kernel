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
     * @var array
     */
    private $resettableServices;

    public function __construct( VersatileScopeInterface $configResolver, ViewManagerInterface $viewManager, array $resettableServices )
    {
        $this->configResolver = $configResolver;
        $this->viewManager = $viewManager;
        $this->resettableServices = $resettableServices;
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
        foreach ( $this->resettableServices as $serviceId )
        {
            $this->container->set( $serviceId, null );
        }
    }
}
