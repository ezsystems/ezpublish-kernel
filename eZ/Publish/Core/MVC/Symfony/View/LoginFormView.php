<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class LoginFormView extends BaseView
{
    /** @var string */
    private $lastUsername;

    /** @var \Symfony\Component\Security\Core\Exception\AuthenticationException|null */
    private $lastAuthenticationException;

    public function getLastUsername(): ?string
    {
        return $this->lastUsername;
    }

    public function setLastUsername(?string $username): void
    {
        $this->lastUsername = $username;
    }

    public function getLastAuthenticationException(): ?AuthenticationException
    {
        return $this->lastAuthenticationException;
    }

    public function setLastAuthenticationError(?AuthenticationException $authenticationException): void
    {
        $this->lastAuthenticationException = $authenticationException;
    }

    protected function getInternalParameters(): array
    {
        return [
            'last_username' => $this->getLastUsername(),
            'error' => $this->getLastAuthenticationException(),
        ];
    }
}
