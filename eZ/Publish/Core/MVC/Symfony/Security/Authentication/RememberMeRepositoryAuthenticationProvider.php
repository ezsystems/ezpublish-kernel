<?php

/**
 * File containing the RememberMeRepositoryAuthenticationProvider class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RememberMeRepositoryAuthenticationProvider extends RememberMeAuthenticationProvider
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $authenticatedToken = parent::authenticate($token);
        if (empty($authenticatedToken)) {
            throw new AuthenticationException('The token is not supported by this authentication provider.');
        }

        $this->repository->getPermissionResolver()->setCurrentUserReference(
            $authenticatedToken->getUser()->getAPIUser()
        );

        return $authenticatedToken;
    }
}
