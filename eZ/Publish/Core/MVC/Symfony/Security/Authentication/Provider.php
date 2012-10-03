<?php
/**
 * File containing the Provider class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface,
    eZ\Publish\Core\MVC\Symfony\Security\User\APIUserProviderInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface,
    Symfony\Component\Security\Core\Exception\AuthenticationException;

class Provider implements AuthenticationProviderInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Security\User\APIUserProviderInterface
     */
    protected $userProvider;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $lazyRepository;

    public function __construct( APIUserProviderInterface $userProvider, \Closure $lazyRepository )
    {
        $this->userProvider = $userProvider;
        $this->lazyRepository = $lazyRepository;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        $lazyRepository = $this->lazyRepository;
        return $lazyRepository();
    }

    /**
     * Attempts to authenticates a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate( TokenInterface $token )
    {
        if ( !$this->supports( $token ) )
            return null;

        $module = $token->getAttribute( 'module' );
        $function = $token->getAttribute( 'function' );
        $userId = $token->getAttribute( 'userId' );

        $user = $this->userProvider->loadUserByUsername( $userId );
        if ( $user )
        {
            $apiUser = $user->getUserObject();
            $this->getRepository()->setCurrentUser( $apiUser );

            if ( !$this->getRepository()->hasAccess( $module, $function ) )
                throw new AuthenticationException( "Access to $module/$function denied for user #{$apiUser->id} ({$apiUser->login})" );

            $authenticatedToken = new Token( $module, $function, $apiUser->id/*, array( 'ROLE_EZ_USER' )*/ );
            $authenticatedToken->setUser( $user );
            $authenticatedToken->setAuthenticated( true );
            return $authenticatedToken;
        }

        throw new AuthenticationException( 'The eZ Publish user could not be retrieved from the session' );
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return Boolean true if the implementation supports the Token, false otherwise
     */
    public function supports( TokenInterface $token )
    {
        return $token instanceof Token;
    }
}
