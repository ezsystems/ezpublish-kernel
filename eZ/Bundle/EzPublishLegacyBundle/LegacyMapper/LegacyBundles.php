<?php
/**
 * File containing the LegacyBundles class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Enables extensions from LegacyBundle in the Legacy Kernel
 */
class LegacyBundles implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var array
     */
    private $options;

    /**
     * Disables the feature when set using setEnabled()
     *
     * @var bool
     */
    private $enabled = true;

    public function __construct(
        ConfigResolverInterface $configResolver,
        array $options = array()
    )
    {
        $this->configResolver = $configResolver;
        $this->options = $options;
    }

    /**
     * Toggles the feature
     *
     * @param bool $isEnabled
     */
    public function setEnabled( $isEnabled )
    {
        $this->enabled = (bool)$isEnabled;
    }

    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL => array( "onBuildKernel", 128 )
        );
    }

    /**
     * Adds settings to the parameters that will be injected into the legacy kernel
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent $event
     *
     * @todo Cache computed settings somehow
     */
    public function onBuildKernel( PreBuildKernelEvent $event )
    {
        if ( !$this->enabled )
        {
            return;
        }

        if ( !isset( $this->options['extensions'] ) )
        {
            return;
        }

        $settings = array( "site.ini/ExtensionSettings/ActiveExtensions" => $this->options['extensions'] );

        $event->getParameters()->set(
            "injected-merge-settings",
            $settings + (array)$event->getParameters()->get( "injected-merge-settings" )
        );
    }
}
