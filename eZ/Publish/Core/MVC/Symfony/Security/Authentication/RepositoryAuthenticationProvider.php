<?php
/**
 * File containing the RepositoryAuthenticationProvider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Security\User as EzUser;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

class RepositoryAuthenticationProvider extends DaoAuthenticationProvider
{
    /**
     * @var \Closure
     */
    private $lazyRepository;

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

    protected function checkAuthentication( UserInterface $user, UsernamePasswordToken $token )
    {
        if ( !$user instanceof EzUser )
        {
            return parent::checkAuthentication( $user, $token );
        }

        // $currentUser can either be an instance of UserInterface or just the username (e.g. during form login).
        /** @var EzUser|string $currentUser */
        $currentUser = $token->getUser();
        if ( $currentUser instanceof UserInterface )
        {
            if ( $currentUser->getPassword() !== $user->getPassword() )
            {
                throw new BadCredentialsException( 'The credentials were changed from another session.' );
            }

            $apiUser = $currentUser->getAPIUser();
        }
        else
        {
            try
            {
                $apiUser = $this->getRepository()->getUserService()->loadUserByCredentials( $token->getUsername(), $token->getCredentials() );
            }
            catch ( NotFoundException $e )
            {
                throw new BadCredentialsException( 'Invalid credentials', 0, $e );
            }
        }

        // Finally inject current user in the Repository
        $this->getRepository()->setCurrentUser( $apiUser );
    }
}
