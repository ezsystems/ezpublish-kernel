<?php

/**
 * File containing the RepositoryAuthenticationProvider class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzUserInterface;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

class RepositoryAuthenticationProvider extends DaoAuthenticationProvider
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        if (!$user instanceof EzUserInterface) {
            return parent::checkAuthentication($user, $token);
        }

        $apiUser = $user->getAPIUser();

        // $currentUser can either be an instance of UserInterface or just the username (e.g. during form login).
        /** @var EzUserInterface|string $currentUser */
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getAPIUser()->passwordHash !== $apiUser->passwordHash) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }

            $apiUser = $currentUser->getAPIUser();
        } else {
            $credentialsValid = $this->repository->getUserService()->checkUserCredentials($apiUser, $token->getCredentials());

            if (!$credentialsValid) {
                throw new BadCredentialsException('Invalid credentials', 0);
            }
        }

        // Finally inject current user in the Repository
        $this->repository->setCurrentUser($apiUser);
    }
}
