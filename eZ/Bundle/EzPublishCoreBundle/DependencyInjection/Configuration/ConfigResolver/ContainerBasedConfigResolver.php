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

abstract class ContainerBasedConfigResolver implements ConfigResolverInterface, ContainerAwareInterface
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

    public function getParameter($paramName, $namespace = null, $scope = null)
    {
        list($namespace, $scope) = $this->resolveNamespaceAndScope($namespace, $scope);

        $scopeRelativeParamName = $this->getScopeRelativeParamName($paramName, $namespace, $scope);
        if ($this->container->hasParameter($scopeRelativeParamName)) {
            return $this->container->getParameter($scopeRelativeParamName);
        }

        throw new ParameterNotFoundException($paramName, $namespace, [$scope]);
    }

    public function hasParameter($paramName, $namespace = null, $scope = null): bool
    {
        return $this->container->hasParameter($this->resolveScopeRelativeParamName($paramName, $namespace, $scope));
    }

    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    public function setDefaultNamespace($defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    private function resolveScopeRelativeParamName($paramName, $namespace = null, $scope = null): string
    {
        return $this->getScopeRelativeParamName($paramName, ...$this->resolveNamespaceAndScope($namespace, $scope));
    }

    private function resolveNamespaceAndScope($namespace = null, $scope = null): array
    {
        return [$namespace ?: $this->getDefaultNamespace(), $this->scope];
    }

    private function getScopeRelativeParamName(string $paramName, string $namespace, string $scope): string
    {
        return "$namespace.$scope.$paramName";
    }
}
