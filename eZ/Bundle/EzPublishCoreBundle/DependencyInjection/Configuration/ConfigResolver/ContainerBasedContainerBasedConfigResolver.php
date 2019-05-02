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
    protected $defaultNamespace;

    public function getParameter($paramName, $namespace = null, $scope = null)
    {
        $resolvedNamespace = $this->resolveNamespace($namespace);
        $resolvedScope = $this->resolveScope($scope);
        $resolvedParamName = $this->resolveParamName($paramName, $resolvedNamespace, $resolvedScope);

        if ($this->container->hasParameter($resolvedParamName)) {
            return $this->container->getParameter($resolvedParamName);
        }

        throw new ParameterNotFoundException($paramName, $resolvedNamespace, [$resolvedScope]);
    }

    public function hasParameter($paramName, $namespace = null, $scope = null)
    {
        $resolvedNamespace = $this->resolveNamespace($namespace);
        $resoledScope = $this->resolveScope($scope);
        $resolvedParamName = $this->resolveParamName($paramName, $resolvedNamespace, $resoledScope);

        return $this->container->hasParameter($resolvedParamName);
    }

    public function setDefaultNamespace($defaultNamespace)
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    protected function resolveNamespace(string $namespace = null): string
    {
        return $namespace ?: $this->getDefaultNamespace();
    }

    protected function resolveParamName(string $paramName, string $namespace, string $scope): string
    {
        return "$namespace.$scope.$paramName";
    }

    protected abstract function resolveScope(string $scope = null): string;
}
