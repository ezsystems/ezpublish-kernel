<?php
/**
 * File containing the LegacyEvents class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Legacy;

final class LegacyEvents
{
    /*
     * The PRE_BUILD_LEGACY_KERNEL_WEB occurs right before the build of the legacy
     * kernel web handler. This event allows to inject parameters into the web
     * handler.
     *
     * The event listener method receives a
     * \eZ\Publish\Legacy\Event\PreBuildKernelWebHandlerEvent
     */
    const PRE_BUILD_LEGACY_KERNEL_WEB = 'ezpublish_legacy.build_kernel_web_handler';
}
