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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LegacyKernelListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\MVC\Legacy\Kernel */
    private $legacyKernelClosure;

    /** @var LegacyKernel */
    private $legacyKernel;

    /**
     * @param \Closure $kernelClosure The LegacyKernel closure
     */
    public function __construct( \Closure $kernelClosure )
    {
        $this->legacyKernelClosure = $kernelClosure;
    }

    /**
     * @return LegacyKernel
     */
    protected function getLegacyKernel()
    {
        if ( !isset( $this->legacyKernel ) && isset( $this->legacyKernelClosure ) )
        {
            $kernelClosure = $this->legacyKernelClosure;
            $this->legacyKernel = $kernelClosure();
        }

        return $this->legacyKernel;
    }

    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::PRE_RESET_LEGACY_KERNEL => 'onKernelReset'
        );
    }

    public function onKernelReset()
    {
        \eZINI::resetAllInstances();
    }
}
