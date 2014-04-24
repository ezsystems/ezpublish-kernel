<?php
/**
 * File containing the RestSessionBasedAuthenticationListener class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Security;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User as EzUser;
use eZ\Publish\Core\REST\Server\Exceptions\InvalidUserTypeException;
use eZ\Publish\Core\REST\Server\Exceptions\UserConflictException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Authenticator for REST API, mainly used for session based authentication (session creation resource).
 *
 * Implements \Symfony\Component\Security\Http\Firewall\ListenerInterface to be able to receive the provider key
 * (firewall identifier from configuration).
 */
class RestAuthenticator implements ListenerInterface, AuthenticatorInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    private $authenticationManager;
    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        $providerKey,
        EventDispatcherInterface $dispatcher,
        ConfigResolverInterface $configResolver,
        LoggerInterface $logger = null
    )
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->dispatcher = $dispatcher;
        $this->configResolver = $configResolver;
        $this->logger = $logger;

    }

    /**
     * Doesn't do anything as we don't use this service with main Firewall listener.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function handle( GetResponseEvent $event )
    {
        return;
    }

    public function authenticate( Request $request )
    {
        // If a token already exists and username is the same as the one we request authentication for,
        // then return it and mark it as coming from session.
        $previousToken = $this->securityContext->getToken();
        if (
            $previousToken instanceof TokenInterface
            && $previousToken->getUsername() === $request->attributes->get( 'username' )
        )
        {
            $previousToken->setAttribute( 'isFromSession', true );
            return $previousToken;
        }

        $token = $this->attemptAuthentication( $request );
        if ( !$token instanceof TokenInterface )
        {
            if ( $this->logger )
            {
                $this->logger->error( 'REST: No token could be found in SecurityContext' );
            }

            throw new TokenNotFoundException();
        }

        $this->securityContext->setToken( $token );
        $this->dispatcher->dispatch(
            SecurityEvents::INTERACTIVE_LOGIN,
            new InteractiveLoginEvent( $request, $token )
        );

        // Re-fetch token from SecurityContext since an INTERACTIVE_LOGIN listener might have changed it
        // i.e. when using multiple user providers.
        // @see \eZ\Publish\Core\MVC\Symfony\Security\EventListener\SecurityListener::onInteractiveLogin()
        $token = $this->securityContext->getToken();
        $user = $token->getUser();
        if ( !$user instanceof EzUser )
        {
            if ( $this->logger )
            {
                $this->logger->error( 'REST: Authenticated user must be eZ\Publish\Core\MVC\Symfony\Security\User, got ' . is_string( $user ) ? $user : get_class( $user ) );
            }

            $e = new InvalidUserTypeException( 'Authenticated user is not an eZ User.' );
            $e->setToken( $token );
            throw $e;
        }

        // Check if newly logged in user differs from previous one.
        if ( $this->isUserConflict( $user, $previousToken ) )
        {
            $this->securityContext->setToken( $previousToken );
            throw new UserConflictException();
        }

        return $token;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    private function attemptAuthentication( Request $request )
    {
        return $this->authenticationManager->authenticate(
            new UsernamePasswordToken(
                $request->attributes->get( 'username' ),
                $request->attributes->get( 'password' ),
                $this->providerKey
            )
        );
    }

    /**
     * Checks if newly matched user is conflicting with previously non-anonymous logged in user, if any.
     *
     * @param EzUser $user
     * @param TokenInterface $previousToken
     *
     * @return bool
     */
    private function isUserConflict( EzUser $user, TokenInterface $previousToken = null )
    {
        if ( $previousToken === null || !$previousToken instanceof UsernamePasswordToken )
        {
            return false;
        }

        $previousUser = $previousToken->getUser();
        if ( !$previousUser instanceof EzUser )
        {
            return false;
        }

        $wasAnonymous = $previousUser->getAPIUser()->id == $this->configResolver->getParameter( 'anonymous_user_id' );
        return ( !$wasAnonymous && !$user->isEqualTo( $previousUser ) );
    }
}
