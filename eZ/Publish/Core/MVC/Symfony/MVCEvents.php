<?php
/**
 * File containing the Events class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony;

final class MVCEvents
{
    /**
     * The SITEACCESS event occurs after the SiteAccess matching has occurred.
     * This event gives further control on the matched SiteAccess.
     *
     * The event listener method receives a \eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent
     */
    const SITEACCESS = 'ezpublish.siteaccess';

    /**
     * The PRE_CONTENT_VIEW event occurs right before a view is rendered for a content, via the content view controller.
     * This event is triggered by the view manager and allows you to inject additional parameters to the content view template.
     *
     * The event listener method receives a \eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent
     * @see eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    const PRE_CONTENT_VIEW = 'ezpublish.pre_content_view';

    /**
     * The API_CONTENT_EXCEPTION event occurs when the API throws an exception that could not be caught internally
     * (missing field type, internal error...).
     * It allows further programmatic handling (like rendering a custom view) for the exception thrown.
     *
     * The event listener method receives an \eZ\Publish\Core\MVC\Symfony\Event\APIContentExceptionEvent.
     */
    const API_CONTENT_EXCEPTION = 'ezpublish.api.contentException';
}
