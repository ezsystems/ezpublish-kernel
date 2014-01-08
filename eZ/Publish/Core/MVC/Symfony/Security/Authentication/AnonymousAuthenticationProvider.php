<?php
/**
 * File containing the AnonymousAuthenticationProvider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider as BaseAnonymousProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AnonymousAuthenticationProvider extends BaseAnonymousProvider
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

    public function authenticate( TokenInterface $token )
    {
        $token = parent::authenticate( $token );
        $this->getRepository()->setCurrentUser(
            $this->getRepository()->getUserService()->loadAnonymousUser()
        );
        return $token;
    }
}
