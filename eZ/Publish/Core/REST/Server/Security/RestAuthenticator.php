<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Security;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzUser;
use eZ\Publish\Core\REST\Server\Exceptions\InvalidUserTypeException;
use eZ\Publish\Core\REST\Server\Exceptions\UserConflictException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Authenticator for REST API, mainly used for session based authentication (session creation resource).
 *
 * Implements \Symfony\Component\Security\Http\Firewall\ListenerInterface to be able to receive the provider key
 * (firewall identifier from configuration).
 */
class RestAuthenticator implements ListenerInterface, AuthenticatorInterface
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface */
    private $authenticationManager;
    /** @var string */
    private $providerKey;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $dispatcher;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface */
    private $sessionStorage;

    /** @var \Symfony\Component\Security\Http\Logout\LogoutHandlerInterface[] */
    private $logoutHandlers = [];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        $providerKey,
        EventDispatcherInterface $dispatcher,
        ConfigResolverInterface $configResolver,
        SessionStorageInterface $sessionStorage,
        ?LoggerInterface $logger = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->dispatcher = $dispatcher;
        $this->configResolver = $configResolver;
        $this->sessionStorage = $sessionStorage;
        $this->logger = $logger;
    }

    /**
     * Doesn't do anything as we don't use this service with main Firewall listener.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        return;
    }

    public function authenticate(Request $request)
    {
        // If a token already exists and username is the same as the one we request authentication for,
        // then return it and mark it as coming from session.
        $previousToken = $this->tokenStorage->getToken();
        if (
            $previousToken instanceof TokenInterface
            && $previousToken->getUsername() === $request->attributes->get('username')
        ) {
            $previousToken->setAttribute('isFromSession', true);

            return $previousToken;
        }

        $token = $this->attemptAuthentication($request);
        if (!$token instanceof TokenInterface) {
            if ($this->logger) {
                $this->logger->error('REST: No token could be found in SecurityContext');
            }

            throw new TokenNotFoundException();
        }

        $this->tokenStorage->setToken($token);
        $this->dispatcher->dispatch(
            SecurityEvents::INTERACTIVE_LOGIN,
            new InteractiveLoginEvent($request, $token)
        );

        // Re-fetch token from SecurityContext since an INTERACTIVE_LOGIN listener might have changed it
        // i.e. when using multiple user providers.
        // @see \eZ\Publish\Core\MVC\Symfony\Security\EventListener\SecurityListener::onInteractiveLogin()
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        if (!$user instanceof EzUser) {
            if ($this->logger) {
                $this->logger->error('REST: Authenticated user must be eZ\Publish\Core\MVC\Symfony\Security\User, got ' . is_string($user) ? $user : get_class($user));
            }

            $e = new InvalidUserTypeException('Authenticated user is not an eZ User.');
            $e->setToken($token);
            throw $e;
        }

        // Check if newly logged in user differs from previous one.
        if ($this->isUserConflict($user, $previousToken)) {
            $this->tokenStorage->setToken($previousToken);
            throw new UserConflictException();
        }

        return $token;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    private function attemptAuthentication(Request $request)
    {
        return $this->authenticationManager->authenticate(
            new UsernamePasswordToken(
                $request->attributes->get('username'),
                $request->attributes->get('password'),
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
    private function isUserConflict(EzUser $user, TokenInterface $previousToken = null)
    {
        if ($previousToken === null || !$previousToken instanceof UsernamePasswordToken) {
            return false;
        }

        $previousUser = $previousToken->getUser();
        if (!$previousUser instanceof EzUser) {
            return false;
        }

        $wasAnonymous = $previousUser->getAPIUser()->getUserId() == $this->configResolver->getParameter('anonymous_user_id');
        // TODO: isEqualTo is not on the interface
        return !$wasAnonymous && !$user->isEqualTo($previousUser);
    }

    public function addLogoutHandler(LogoutHandlerInterface $handler)
    {
        $this->logoutHandlers[] = $handler;
    }

    public function logout(Request $request)
    {
        $response = new Response();

        // Manually clear the session through session storage.
        // Session::invalidate() is not called on purpose, to avoid unwanted session migration that would imply
        // generation of a new session id.
        // REST logout must indeed clear the session cookie.
        // See \eZ\Publish\Core\REST\Server\Security\RestLogoutHandler
        $this->sessionStorage->clear();

        $token = $this->tokenStorage->getToken();
        foreach ($this->logoutHandlers as $handler) {
            // Explicitly ignore SessionLogoutHandler as we do session invalidation manually here,
            // through the session storage, to avoid unwanted session migration.
            if ($handler instanceof SessionLogoutHandler) {
                continue;
            }

            $handler->logout($request, $response, $token);
        }

        return $response;
    }
}
