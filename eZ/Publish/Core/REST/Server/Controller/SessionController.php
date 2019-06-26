<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface;
use eZ\Publish\Core\REST\Common\Exceptions\NotFoundException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Exceptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use eZ\Publish\Core\REST\Server\Security\CsrfTokenManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class SessionController extends Controller
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \eZ\Publish\Core\REST\Server\Security\CsrfTokenManager */
    private $csrfTokenManager;

    /** @var string */
    private $csrfTokenIntention;

    public function __construct(
        AuthenticatorInterface $authenticator,
        $tokenIntention,
        CsrfTokenManager $csrfTokenManager = null,
        TokenStorageInterface $csrfTokenStorage = null
    ) {
        $this->authenticator = $authenticator;
        $this->csrfTokenIntention = $tokenIntention;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * Creates a new session based on the credentials provided as POST parameters.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException If the login or password are incorrect or invalid CSRF
     *
     * @return Values\UserSession|Values\Conflict
     */
    public function createSessionAction(Request $request)
    {
        /** @var $sessionInput \eZ\Publish\Core\REST\Server\Values\SessionInput */
        $sessionInput = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );
        $request->attributes->set('username', $sessionInput->login);
        $request->attributes->set('password', $sessionInput->password);

        try {
            $session = $request->getSession();
            if ($session->isStarted() && $this->hasStoredCsrfToken()) {
                $this->checkCsrfToken($request);
            }

            $token = $this->authenticator->authenticate($request);
            $csrfToken = $this->getCsrfToken();

            return new Values\UserSession(
                $token->getUser()->getAPIUser(),
                $session->getName(),
                $session->getId(),
                $csrfToken,
                !$token->hasAttribute('isFromSession')
            );
        } catch (Exceptions\UserConflictException $e) {
            // Already logged in with another user, this will be converted to HTTP status 409
            return new Values\Conflict();
        } catch (AuthenticationException $e) {
            $this->authenticator->logout($request);
            throw new UnauthorizedException('Invalid login or password', $request->getPathInfo());
        } catch (AccessDeniedException $e) {
            $this->authenticator->logout($request);
            throw new UnauthorizedException($e->getMessage(), $request->getPathInfo());
        }
    }

    /**
     * Refresh given session.
     *
     * @param string $sessionId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\Core\REST\Server\Values\UserSession
     */
    public function refreshSessionAction($sessionId, Request $request)
    {
        $session = $request->getSession();

        if ($session === null || !$session->isStarted() || $session->getId() != $sessionId || !$this->hasStoredCsrfToken()) {
            $response = $this->authenticator->logout($request);
            $response->setStatusCode(404);

            return $response;
        }

        $this->checkCsrfToken($request);

        return new Values\UserSession(
            $this->repository->getCurrentUser(),
            $session->getName(),
            $session->getId(),
            $request->headers->get('X-CSRF-Token'),
            false
        );
    }

    /**
     * Deletes given session.
     *
     * @param string $sessionId
     *
     * @return Values\DeletedUserSession
     *
     * @throws NotFoundException
     */
    public function deleteSessionAction($sessionId, Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();
        if (!$session->isStarted() || $session->getId() != $sessionId || !$this->hasStoredCsrfToken()) {
            $response = $this->authenticator->logout($request);
            $response->setStatusCode(404);

            return $response;
        }

        $this->checkCsrfToken($request);

        return new Values\DeletedUserSession($this->authenticator->logout($request));
    }

    /**
     * Tests if a CSRF token is stored.
     *
     * @return bool
     */
    private function hasStoredCsrfToken()
    {
        if ($this->csrfTokenManager === null) {
            return true;
        }

        return $this->csrfTokenManager->hasToken($this->csrfTokenIntention);
    }

    /**
     * Checks the presence / validity of the CSRF token.
     *
     * @param Request $request
     *
     * @throws UnauthorizedException if the token is missing or invalid.
     */
    private function checkCsrfToken(Request $request)
    {
        if ($this->csrfTokenManager === null) {
            return;
        }

        $exception = new UnauthorizedException(
            'Missing or invalid CSRF token',
            $request->getMethod() . ' ' . $request->getPathInfo()
        );

        if (!$request->headers->has('X-CSRF-Token')) {
            throw $exception;
        }

        $csrfToken = new CsrfToken(
            $this->csrfTokenIntention,
            $request->headers->get('X-CSRF-Token')
        );

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw $exception;
        }
    }

    /**
     * Returns the csrf token for REST. The token is generated if it doesn't exist.
     *
     * @return string The csrf token, or an empty string if csrf check is disabled.
     */
    private function getCsrfToken()
    {
        if ($this->csrfTokenManager === null) {
            return '';
        }

        return $this->csrfTokenManager->getToken($this->csrfTokenIntention)->getValue();
    }
}
