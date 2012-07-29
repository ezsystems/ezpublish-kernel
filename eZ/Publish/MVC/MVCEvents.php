<?php
/**
 * File containing the Events class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC;

final class MVCEvents
{
    /**
     * The SITEACCESS event occurs after the SiteAccess matching has occurred.
     * This event gives further control on the matched SiteAccess.
     *
     * The event listener method receives a \eZ\Publish\MVC\Event\PostSiteAccessMatchEvent
     */
    const SITEACCESS = 'ezpublish.siteaccess';

    /**
     * The PRE_CONTENT_VIEW event occurs right before a view is rendered for a content, via the content view controller.
     * This event is triggered by the view manager and allows you to inject additional parameters to the content view template.
     *
     * The event listener method receives a \eZ\Publish\MVC\Event\PreContentViewEvent
     * @see eZ\Publish\MVC\View\Manager
     */
    const PRE_CONTENT_VIEW = 'ezpublish.pre_content_view';

    /*
     * The BUILD_KERNEL_WEB_HANDLER occurs right before the build of the legacy
     * kernel web handler. This event allows to inject parameters into the web
     * handler.
     *
     * The event listener method receives a
     * \eZ\Publish\MVC\Event\PreBuildKernelWebHandlerEvent
     */
    const BUILD_KERNEL_WEB_HANDLER = 'ezpublish.build_kernel_web_handler';
}
