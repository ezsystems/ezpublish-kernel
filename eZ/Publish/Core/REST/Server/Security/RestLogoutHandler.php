<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Security;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Logout handler used by REST session based logout.
 * It forces session cookie clearing.
 */
class RestLogoutHandler implements LogoutHandlerInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if (!$request->attributes->get('is_rest_request')) {
            return;
        }

        $path = '/';
        $domain = null;

        $session = $this->configResolver->getParameter('session');
        if (array_key_exists('cookie_domain', $session)) {
            $domain = $session['cookie_domain'];
        }
        if (array_key_exists('cookie_path', $session)) {
            $path = $session['cookie_path'];
        }

        $response->headers->clearCookie($request->getSession()->getName(), $path, $domain);
    }
}
