<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccessGroup;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @property-read \Symfony\Component\DependencyInjection\ContainerInterface $container
 *
 * @internal
 */
class SiteAccessGroupConfigResolver extends SiteAccessConfigResolver
{
    use ContainerAwareTrait;

    /** @var array */
    protected $siteAccessGroups;

    public function __construct(
        SiteAccess\SiteAccessProviderInterface $siteAccessProvider,
        string $defaultNamespace,
        array $siteAccessGroups
    ) {
        parent::__construct($siteAccessProvider, $defaultNamespace);
        $this->siteAccessGroups = $siteAccessGroups;
    }

    public function hasParameter(string $paramName, ?string $namespace = null, ?string $scope = null): bool
    {
        [$namespace, $scope] = $this->resolveNamespaceAndScope($namespace, $scope);

        if ($this->isSiteAccessGroupScope($scope)) {
            return $this->resolverHasParameterForGroup(new SiteAccessGroup($scope), $paramName, $namespace);
        }

        if (!$this->isSiteAccessScope($scope)) {
            return false;
        }

        $siteAccess = $this->siteAccessProvider->getSiteAccess($scope);
        if (!$this->isSiteAccessSupported($siteAccess)) {
            return false;
        }

        return $this->resolverHasParameter($siteAccess, $paramName, $namespace);
    }

    final public function getParameter(string $paramName, ?string $namespace = null, ?string $scope = null)
    {
        [$namespace, $scope] = $this->resolveNamespaceAndScope($namespace, $scope);

        if ($this->isSiteAccessGroupScope($scope)) {
            return $this->getParameterFromResolverForGroup(new SiteAccessGroup($scope), $paramName, $namespace);
        }

        if (!$this->isSiteAccessScope($scope)) {
            throw new ParameterNotFoundException($paramName, $namespace, [$scope]);
        }

        $siteAccess = $this->siteAccessProvider->getSiteAccess($scope);
        if (!$this->isSiteAccessSupported($siteAccess)) {
            throw new ParameterNotFoundException($paramName, $namespace, [$scope]);
        }

        return $this->getParameterFromResolver($siteAccess, $paramName, $namespace);
    }

    protected function resolverHasParameter(SiteAccess $siteAccess, string $paramName, string $namespace): bool
    {
        foreach ($siteAccess->groups as $group) {
            $groupScopeParamName = $this->resolveScopeRelativeParamName($paramName, $namespace, $group->getName());
            if ($this->container->hasParameter($groupScopeParamName)) {
                return true;
            }
        }

        return false;
    }

    protected function resolverHasParameterForGroup(SiteAccessGroup $siteAccessGroup, string $paramName, string $namespace): bool
    {
        $groupScopeParamName = $this->resolveScopeRelativeParamName($paramName, $namespace, $siteAccessGroup->getName());
        if ($this->container->hasParameter($groupScopeParamName)) {
            return true;
        }

        return false;
    }

    protected function getParameterFromResolver(SiteAccess $siteAccess, string $paramName, string $namespace)
    {
        $triedScopes = [];

        foreach ($siteAccess->groups as $group) {
            $groupScopeParamName = $this->resolveScopeRelativeParamName($paramName, $namespace, $group->getName());
            if ($this->container->hasParameter($groupScopeParamName)) {
                return $this->container->getParameter($groupScopeParamName);
            }

            $triedScopes[] = $group->getName();
        }

        throw new ParameterNotFoundException($paramName, $namespace, $triedScopes);
    }

    protected function getParameterFromResolverForGroup(SiteAccessGroup $siteAccessGroup, string $paramName, string $namespace)
    {
        $groupScopeParamName = $this->resolveScopeRelativeParamName($paramName, $namespace, $siteAccessGroup->getName());
        if ($this->container->hasParameter($groupScopeParamName)) {
            return $this->container->getParameter($groupScopeParamName);
        }

        throw new ParameterNotFoundException($paramName, $namespace, [$siteAccessGroup]);
    }

    private function isSiteAccessGroupScope($scope): bool
    {
        return array_key_exists($scope, $this->siteAccessGroups);
    }
}
