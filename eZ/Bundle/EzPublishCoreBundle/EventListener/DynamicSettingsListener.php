<?php
/**
 * File containing the DynamicSettingsListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DynamicSettingsListener extends ContainerAware implements EventSubscriberInterface
{
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

    public function __construct( array $resettableServiceIds, array $dynamicSettingsServiceIds )
    {
        $this->resettableServiceIds = $resettableServiceIds;
        $this->dynamicSettingsServiceIds = $dynamicSettingsServiceIds;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 254 ),
            MVCEvents::CONFIG_SCOPE_CHANGE => array( 'onConfigScopeChange', 90 ),
            MVCEvents::CONFIG_SCOPE_RESTORE => array( 'onConfigScopeChange', 90 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        if ( $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
        {
            return;
        }

        $this->resetDynamicSettings();
    }

    public function onConfigScopeChange( ScopeChangeEvent $event )
    {
        $this->resetDynamicSettings();
    }

    /**
     * Ensure that dynamic settings are correctly reset,
     * so that services that rely on those are correctly updated
     */
    private function resetDynamicSettings()
    {
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
