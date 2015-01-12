<?php
/**
 * File containing the LegacyKernelListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreResetLegacyKernelEvent;
use eZINI;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resets eZINI when the Legacy Kernel is reset.
 */
class LegacyKernelListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            LegacyEvents::PRE_RESET_LEGACY_KERNEL => 'onKernelReset',
            ConsoleEvents::COMMAND => 'onConsoleCommand'
        ];
    }

    public function onKernelReset( PreResetLegacyKernelEvent $event )
    {
        $event->getLegacyKernel()->runCallback(
            function() {
                eZINI::resetAllInstances();
            },
            true,
            false
        );
    }

    public function onConsoleCommand( ConsoleCommandEvent $event )
    {
        $legacyHandlerCLI = $this->container->get( 'ezpublish_legacy.kernel_handler.cli' );
        $this->container->set( 'ezpublish_legacy.kernel.lazy', null );
        $this->container->set( 'ezpublish_legacy.kernel_handler', $legacyHandlerCLI );
        $this->container->set( 'ezpublish_legacy.kernel_handler.web', $legacyHandlerCLI );
    }
}
