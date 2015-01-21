<?php
/**
 * File containing the ResponseListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ezxFormToken;

/**
 * Adds legacy CSRF Token when needed.
 *
 * In the case of browsing legacy modules through Symfony (i.e. NOT legacy mode), result from legacy kernel is filtered
 * for forms, but meta tags containing the token might not be present, e.g. when using a Twig layout to render
 * legacy modules.
 *
 * @see https://jira.ez.no/browse/EZP-23074
 */
class CsrfTokenResponseListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse'
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse( FilterResponseEvent $event )
    {
        $response = $event->getResponse();
        if ( !$this->isLegacyResponse( $response ) )
        {
            return;
        }

        // Filter out once again to add meta tags containing CSRF token (i.e. for legacy javascripts).
        $response->setContent( ezxFormToken::output( $response->getContent(), false ) );
    }

    /**
     * Checks if $response coming from LegacyKernelController having a moduleResult.
     * Presence of moduleResult in response clearly indicates that a Twig layout is used, so no CSRF meta tag is present.
     *
     * @see \eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager::generateResponseFromModuleResult()
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return bool
     */
    private function isLegacyResponse( Response $response )
    {
        return $response instanceof LegacyResponse && $response->getModuleResult();
    }
}
