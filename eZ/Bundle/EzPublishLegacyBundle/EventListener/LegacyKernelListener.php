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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resets eZINI when the Legacy Kernel is reset.
 */
class LegacyKernelListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::PRE_RESET_LEGACY_KERNEL => 'onKernelReset'
        );
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
}
