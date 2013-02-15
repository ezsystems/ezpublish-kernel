<?php
/**
 * File containing the Provider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\PreAuthenticatedAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class Provider extends PreAuthenticatedAuthenticationProvider
{
    /**
     * @var \Closure
     */
    protected $lazyRepository;

    public function setLazyRepository( \Closure $lazyRepository )
    {
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
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException if the authentication fails
     */
    public function authenticate( TokenInterface $token )
    {
        if ( !$this->supports( $token ) )
            return null;

        $authenticatedToken = parent::authenticate( $token );
        if ( $authenticatedToken instanceof PreAuthenticatedToken )
        {
            $user = $authenticatedToken->getUser();
            if ( !$user instanceof User )
                throw new AuthenticationException( 'Invalid eZ Publish user. Expected type is eZ\\Publish\\Core\\MVC\\Symfony\\Security\\User. Got ' . get_class( $user ) );

            // Inject current user in the repository
            $this->getRepository()->setCurrentUser( $user->getAPIUser() );

            return $authenticatedToken;
        }

        throw new AuthenticationException( 'The eZ Publish user could not be retrieved from the session' );
    }
}
