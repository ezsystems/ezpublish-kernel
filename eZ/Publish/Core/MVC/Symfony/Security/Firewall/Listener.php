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
    Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener,
    Symfony\Component\HttpFoundation\Request;

class Listener extends AbstractAuthenticationListener
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

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
        $routeName = $request->attributes->get( '_route' );
        if ( !$this->isRouteSupported( $request->attributes->get( '_route' ) ) )
            return false;

        list( $module, $function ) = $this->supportedRoutes[$routeName];
        return $this->repository->hasAccess( $module, $function ) !== false;
    }

    private function isRouteSupported( $routeName )
    {
        return isset( $this->supportedRoutes[$routeName] );
    }

    /**
     * Performs authentication.
     *
     * @todo Not implemented yet in 5.0, still relies on legacy user/login.
     * @see eZ\Publish\Core\MVC\Legacy\Security\Firewall\LegacyListener
     *
     * @param Request $request A Request instance
     *
     * @return TokenInterface|Response|null The authenticated token, null if full authentication is not possible, or a Response
     *
     * @throws AuthenticationException if the authentication fails
     */
    protected function attemptAuthentication(Request $request)
    {
        return null;
    }
}
