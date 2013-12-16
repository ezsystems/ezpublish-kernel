<?php
/**
 * File containing the CsrfListener class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Bundle\EzPublishRestBundle\RestEvents;

class CsrfListener implements EventSubscriberInterface
{
    /**
     * Name of the HTTP header containing CSRF token.
     */
    const CSRF_TOKEN_HEADER = "X-CSRF-Token";

    /**
     * @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface
     */
    private $csrfProvider;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $csrfEnabled;

    /**
     * @var bool
     */
    private $csrfTokenIntention;

    /**
     * @param \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface $csrfProvider
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param bool $csrfEnabled
     * @param string $csrfTokenIntention
     */
    public function __construct(
        CsrfProviderInterface $csrfProvider,
        EventDispatcherInterface $eventDispatcher,
        $csrfEnabled,
        $csrfTokenIntention )
    {
        $this->csrfProvider = $csrfProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->csrfEnabled = $csrfEnabled;
        $this->csrfTokenIntention = $csrfTokenIntention;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }

    /**
     * This method validates CSRF token if CSRF protection is enabled.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function onKernelRequest( GetResponseEvent $event )
    {
        if ( !$event->getRequest()->attributes->get( 'is_rest_request' ) )
        {
            return;
        }

        if ( !$this->csrfEnabled )
        {
            return;
        }

        // skip CSRF validation if no session is running
        if ( !$event->getRequest()->getSession()->isStarted() )
        {
            return;
        }

        if ( $this->isMethodSafe( $event->getRequest()->getMethod() ) )
        {
            return;
        }

        if ( $this->isLoginRequest( $event->getRequest()->get( "_route" ) ) )
        {
            return;
        }

        if ( !$this->checkCsrfToken( $event->getRequest() ) )
        {
            throw new UnauthorizedException(
                "Missing or invalid CSRF token",
                $event->getRequest()->getMethod() . " " . $event->getRequest()->getPathInfo()
            );
        }

        // Dispatching event so that CSRF token intention can be injected into Legacy Stack
        $this->eventDispatcher->dispatch( RestEvents::REST_CSRF_TOKEN_VALIDATED );
    }

    /**
     * @param string $method
     * @return bool
     */
    protected function isMethodSafe( $method )
    {
        return in_array( $method, array( 'GET', 'HEAD', 'OPTIONS' ) );
    }

    /**
     * @param string $route
     * @return bool
     */
    protected function isLoginRequest( $route )
    {
        // TODO: add CSRF token to protect against force-login attacks
        return $route == "ezpublish_rest_createSession";
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return bool
     */
    protected function checkCsrfToken( Request $request )
    {
        if ( !$request->headers->has( self::CSRF_TOKEN_HEADER ) )
        {
            return false;
        }

        return $this->csrfProvider->isCsrfTokenValid(
            $this->csrfTokenIntention,
            $request->headers->get( self::CSRF_TOKEN_HEADER )
        );
    }
}
