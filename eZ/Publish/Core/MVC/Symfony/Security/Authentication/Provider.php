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
    Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface,
    Symfony\Component\Security\Core\Exception\AuthenticationException;

class Provider implements AuthenticationProviderInterface
{
    /**
     * @var \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    protected $userProvider;

    public function __construct( UserProviderInterface $userProvider )
    {
        $this->userProvider = $userProvider;
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
        // Note: loadUserByUsername() is a generic method name. We keep using it to be consistent with the interface.
        $user = $this->userProvider->loadUserByUsername( $token->getUserId() );
        if ( $user )
        {
            $authenticatedToken = new Token( $token->getUserId() );
            $authenticatedToken->setUser( $user );
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
