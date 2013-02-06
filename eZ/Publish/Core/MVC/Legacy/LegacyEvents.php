<?php
/**
 * File containing the LegacyEvents class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy;

final class LegacyEvents
{
    /*
     * The PRE_BUILD_LEGACY_KERNEL_WEB occurs right before the build of the legacy
     * kernel web handler. This event allows to inject parameters into the web
     * handler.
     *
     * Listen to this event for pure web related stuff (i.e. sessions or request related).
     * If you need your settings to be injected both in web and CLI context, listen to PRE_BUILD_LEGACY_KERNEL
     *
     * The event listener method receives a
     * \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent
     */
    const PRE_BUILD_LEGACY_KERNEL_WEB = 'ezpublish_legacy.build_kernel_web_handler';

    /**
     * The PRE_BUID_LEGACY_KERNEL occurs right before the build of the legacy handler (whatever the handler is used).
     * This event allows to inject parameters in the legacy kernel (such as INI settings).
     *
     * The event listener method receives a
     * \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent
     */
    const PRE_BUILD_LEGACY_KERNEL = 'epzublish_legacy.build_kernel';
}
