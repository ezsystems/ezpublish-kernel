<?php

/**
 * File containing the RepositoryAuthenticationProvider class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\User as EzUser;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

class RepositoryAuthenticationProvider extends DaoAuthenticationProvider
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        if (!$user instanceof EzUser) {
            return parent::checkAuthentication($user, $token);
        }

        // $currentUser can either be an instance of UserInterface or just the username (e.g. during form login).
        /** @var EzUser|string $currentUser */
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }

            $apiUser = $currentUser->getAPIUser();
        } else {
            try {
                $apiUser = $this->repository->getUserService()->loadUserByCredentials($token->getUsername(), $token->getCredentials());
            } catch (NotFoundException $e) {
                throw new BadCredentialsException('Invalid credentials', 0, $e);
            }
        }

        // Finally inject current user in the Repository
        $this->repository->setCurrentUser($apiUser);
    }
}
