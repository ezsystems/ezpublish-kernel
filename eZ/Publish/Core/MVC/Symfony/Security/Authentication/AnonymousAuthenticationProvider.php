<?php
/**
 * File containing the AnonymousAuthenticationProvider class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider as BaseAnonymousProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AnonymousAuthenticationProvider extends BaseAnonymousProvider
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function setRepository( Repository $repository )
    {
        $this->repository = $repository;
    }

    public function setConfigResolver( ConfigResolverInterface $configResolver )
    {
        $this->configResolver = $configResolver;
    }

    public function authenticate( TokenInterface $token )
    {
        $token = parent::authenticate( $token );
        $this->repository->setCurrentUser(
            $this->repository->getUserService()->loadUser(
                $this->configResolver->getParameter( 'anonymous_user_id' )
            )
        );
        return $token;
    }
}
