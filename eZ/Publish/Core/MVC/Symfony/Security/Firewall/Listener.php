<?php
/**
 * File containing the firewall Listener class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Firewall;

use eZ\Publish\API\Repository\Repository,
    eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter,
    eZ\Publish\API\Repository\Values\User\User as APIUser,
    eZ\Publish\Core\MVC\Symfony\Security\Authentication\Token,
    Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener,
    Symfony\Component\HttpFoundation\Request;

abstract class Listener extends AbstractAuthenticationListener
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var array Route names that are supported by current firewall listener
     */
    private $supportedRoutes;

    public function setRepository( Repository $repository )
    {
        $this->repository = $repository;
        $this->supportedRoutes = array(
            UrlAliasRouter::URL_ALIAS_ROUTE_NAME    => array( 'content', 'read' )
        );
    }

    protected function requiresAuthentication( Request $request )
    {
        // TODO : Following implementation is really ugly and temporary.
        if ( !$this->isRouteSupported( $request->attributes->get( '_route' ) ) )
            return false;

        // Always return true since any user must be authenticated, including anonymous user.

        return $this->getCurrentUserId( $request ) === null;
    }

    /**
     * Returns the current eZ Publish user ID when applicable (user already connected) or null (user not connected)
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return int|null
     */
    abstract protected function getCurrentUserId( Request $request );

    private function isRouteSupported( $routeName )
    {
        return isset( $this->supportedRoutes[$routeName] );
    }

    /**
     * Performs authentication.
     *
     * @param Request $request A Request instance
     *
     * @return TokenInterface|Response|null The authenticated token, null if full authentication is not possible, or a Response
     *
     * @throws AuthenticationException if the authentication fails
     */
    protected function attemptAuthentication( Request $request )
    {
        $routeName = $request->attributes->get( '_route' );
        list( $module, $function ) = $this->supportedRoutes[$routeName];

        $token = new Token( $module, $function, $this->getCurrentUserId( $request ) );
        return $this->authenticationManager->authenticate( $token );
    }
}
