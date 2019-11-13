<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @property-read \Symfony\Component\DependencyInjection\ContainerInterface $container
 */
abstract class ContainerConfigResolver implements ConfigResolverInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var string */
    private $scope;

    /** @var string */
    private $defaultNamespace;

    public function __construct(string $scope, string $defaultNamespace)
    {
        $this->scope = $scope;
        $this->defaultNamespace = $defaultNamespace;
    }

    public function getParameter(string $paramName, ?string $namespace = null, ?string $scope = null)
    {
        [$namespace, $scope] = $this->resolveNamespaceAndScope($namespace, $scope);
        $scopeRelativeParamName = $this->getScopeRelativeParamName($paramName, $namespace, $scope);
        if ($this->container->hasParameter($scopeRelativeParamName)) {
            return $this->container->getParameter($scopeRelativeParamName);
        }

        throw new ParameterNotFoundException($paramName, $namespace, [$scope]);
    }

    public function hasParameter(string $paramName, ?string $namespace = null, ?string $scope = null): bool
    {
        return $this->container->hasParameter($this->resolveScopeRelativeParamName($paramName, $namespace, $scope));
    }

    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    private function resolveScopeRelativeParamName(string $paramName, string $namespace = null, string $scope = null): string
    {
        return $this->getScopeRelativeParamName($paramName, ...$this->resolveNamespaceAndScope($namespace, $scope));
    }

    private function resolveNamespaceAndScope(string $namespace = null, string $scope = null): array
    {
        return [$namespace ?: $this->getDefaultNamespace(), $scope ?? $this->scope];
    }

    private function getScopeRelativeParamName(string $paramName, string $namespace, string $scope): string
    {
        return "$namespace.$scope.$paramName";
    }
}
