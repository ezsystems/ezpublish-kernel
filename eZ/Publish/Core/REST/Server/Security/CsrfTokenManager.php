<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManager as BaseCsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class CsrfTokenManager extends BaseCsrfTokenManager
{
    /** @var \Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface */
    private $storage;

    /** @var string */
    private $namespace;

    public function __construct(
        TokenGeneratorInterface $generator = null,
        TokenStorageInterface $storage = null,
        RequestStack $requestStack = null)
    {
        $this->storage = $storage ?: new NativeSessionTokenStorage();
        $this->namespace = $this->resolveNamespace($requestStack);

        parent::__construct($generator, $this->storage, $this->namespace);
    }

    /**
     * Tests if a CSRF token is stored.
     *
     * @param string $tokenId
     * @return bool
     */
    public function hasToken($tokenId)
    {
        return $this->storage->hasToken($this->namespace . $tokenId);
    }

    /**
     * Resolves token namespace.
     *
     * @param RequestStack $requestStack
     * @return string
     */
    private function resolveNamespace(RequestStack $requestStack = null)
    {
        if ($requestStack !== null && ($request = $requestStack->getMasterRequest())) {
            return $request->isSecure() ? 'https-' : '';
        }

        return !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']) ? 'https-' : '';
    }
}
