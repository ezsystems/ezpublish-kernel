<?php
/**
 * File containing the InteractiveLoginListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Security\Firewall;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Listener meant to cleanup user session when anonymous user has been forced in authentication token
 * (e.g. when user has been disabled/removed while he was browsing).
 * In this case we need to properly remove the is_logged_in cookie and the user id stored in session.
 *
 * @see https://jira.ez.no/browse/EZP-21520
 * @see eZ\Publish\Core\MVC\Symfony\Security\Authentication\Provider::authenticate()
 */
class LoginCleanupListener implements EventSubscriberInterface
{
    private $needsCookieCleanup = false;

    /**
     * Removes current userId stored in session if needed.
     *
     * @param InteractiveLoginEvent $e
     */
    public function onInteractiveLogin( InteractiveLoginEvent $e )
    {
        $request = $e->getRequest();
        if ( !$e->getAuthenticationToken()->isAuthenticated() && $request->cookies->has( 'is_logged_in' ) )
        {
            $request->getSession()->invalidate();
            $this->needsCookieCleanup = true;
        }
    }

    /**
     * Removes is_logged_in cookie if needed.
     *
     * @param FilterResponseEvent $e
     */
    public function onFilterResponse( FilterResponseEvent $e )
    {
        if ( $e->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
            return;

        if ( $this->needsCookieCleanup )
        {
            $e->getResponse()->headers->clearCookie( 'is_logged_in' );
            $this->needsCookieCleanup = false;
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            KernelEvents::RESPONSE => 'onFilterResponse'
        );
    }
}
