<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SecurityPass;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzUserInterface;
use JMS\TranslationBundle\Logger\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

class RepositoryAuthenticationProvider extends DaoAuthenticationProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var float|null */
    private $constantAuthTime;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    public function setConstantAuthTime(float $constantAuthTime)
    {
        $this->constantAuthTime = $constantAuthTime;
    }

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

    public function authenticate(TokenInterface $token)
    {
        $startTime = $this->startConstantTimer();

        try {
            $result = parent::authenticate($token);
        } catch (\Exception $e) {
            $this->sleepUsingConstantTimer($startTime);
            throw $e;
        }

        $this->sleepUsingConstantTimer($startTime);

        return $result;
    }

    private function startConstantTimer()
    {
        return microtime(true);
    }

    private function sleepUsingConstantTimer(float $startTime): void
    {
        if ($this->constantAuthTime <= 0.0) {
            return;
        }

        $remainingTime = $this->constantAuthTime - (microtime(true) - $startTime);
        if ($remainingTime > 0) {
            usleep($remainingTime * 1000000);
        } elseif ($this->logger) {
            $this->logger->warning(
                sprintf(
                    'Authentication took longer than the configured constant time. Consider increasing the value of %s',
                    SecurityPass::CONSTANT_AUTH_TIME_SETTING
                ),
                [get_class($this)]
            );
        }
    }
}
