<?php
/**
 * File containing the LegacyKernelListener class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
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
            }
        );
    }
}
