<?php

/**
 * File containing the Events class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     *
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

    /**
     * The API_SIGNAL event occurs when the SignalSlot repository services emit a signal.
     * This make it possible to react to it, depending on which signal is emitted.
     *
     * All available signals can be found under eZ\Publish\Core\SignalSlot\Signal namespace.
     *
     * The event listener method receives a eZ\Publish\Core\MVC\Symfony\Event\SignalEvent instance.
     */
    const API_SIGNAL = 'ezpublish.api.signal';

    /**
     * CONFIG_SCOPE_CHANGE event occurs when configuration scope is changed (e.g. for content preview in a given siteaccess).
     *
     * The event listener method receives a eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent instance.
     */
    const CONFIG_SCOPE_CHANGE = 'ezpublish.config.scope_change';

    /**
     * CONFIG_SCOPE_RESTORE event occurs when original configuration scope is restored.
     * It always happens after a scope change (see CONFIG_SCOPE_CHANGE).
     *
     * The event listener method receives a eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent instance.
     */
    const CONFIG_SCOPE_RESTORE = 'ezpublish.config.scope_restore';

    /**
     * INTERACTIVE_LOGIN event occurs when a user has been authenticated by a foreign user provider.
     * Listening to this event gives a chance to retrieve a valid API user to be injected in repository.
     *
     * The event listener method receives a eZ\Publish\Core\MVC\Symfony\Event\InteractiveLoginEvent instance.
     */
    const INTERACTIVE_LOGIN = 'ezpublish.security.interactive_login';

    /**
     * ROUTE_REFERENCE_GENERATION event occurs when a RouteReference is generated, and gives an opportunity to
     * alter the RouteReference, e.g. by adding parameters.
     *
     * The event listener method receives a eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent instance.
     */
    const ROUTE_REFERENCE_GENERATION = 'ezpublish.routing.reference_generation';

    /**
     * CACHE_CLEAR_CONTENT event occurs when cache needs to be cleared for a content.
     * It gives the opportunity to add related locations to clear (aka "smart cache clearing").
     *
     * The event listener method receives a eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent instance.
     *
     * @deprecated Since 6.12, not triggered anymore when using ezplatform-http-cache package.
     */
    const CACHE_CLEAR_CONTENT = 'ezpublish.cache_clear.content';
}
