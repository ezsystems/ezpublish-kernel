<?php
/**
 * File containing the LegacyEvents class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
    const PRE_BUILD_LEGACY_KERNEL = 'ezpublish_legacy.build_kernel';

    /**
     * The POST_BUILD_LEGACY_KERNEL event occurs after legacy kernel has been built (whatever handler is used).
     *
     * The event listener method receives a \eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent
     */
    const POST_BUILD_LEGACY_KERNEL = 'ezpublish_legacy.post_build_kernel';

    /**
     * The PRE_RESET_LEGACY_KERNEL event occurs before the legacy kernel is reset (unset from the container)
     *
     * Event listeners receive a PreResetKernelEvent object that gives access to the legacy kernel.
     */
    const PRE_RESET_LEGACY_KERNEL = 'ezpublish_legacy.pre_reset_legacy_kernel';
}
