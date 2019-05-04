<?php

declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;

abstract class SiteAccessBasedConfigResolver implements VersatileScopeInterface, SiteAccessAware
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface
     */
    protected $siteAccessProvider;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    protected $currentSiteAccess;

    /**
     * @var string
     */
    protected $defaultScope;

    /**
     * @var string
     */
    protected $defaultNamespace;

    public function __construct(SiteAccess\SiteAccessProviderInterface $siteAccessProvider, string $defaultNamespace)
    {
        $this->siteAccessProvider = $siteAccessProvider;
        $this->defaultNamespace = $defaultNamespace;
    }

    public function hasParameter($paramName, $namespace = null, $scope = null)
    {
        list($namespace, $scope) = $this->resolveNamespaceAndScope($namespace, $scope);
        if (!$this->isSiteAccessScope($scope)) {
            return false;
        }

        $siteAccess = $this->siteAccessProvider->getSiteAccess($scope);
        if (!$this->isSiteAccessSupported($siteAccess)) {
            return false;
        }

        return $this->doHasParameter($siteAccess, $namespace, $scope);
    }

    public function getParameter($paramName, $namespace = null, $scope = null)
    {
        list($namespace, $scope) = $this->resolveNamespaceAndScope($namespace, $scope);

        if (!$this->isSiteAccessScope($scope)) {
            throw new ParameterNotFoundException($paramName, $namespace, [$scope]);
        }

        $siteAccess = $this->siteAccessProvider->getSiteAccess($scope);
        if (!$this->isSiteAccessSupported($siteAccess)) {
            throw new ParameterNotFoundException($paramName, $namespace, [$scope]);
        }

        return $this->doGetParameter($siteAccess, $paramName, $namespace);
    }

    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    public function setDefaultNamespace($defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * Returns current default scope.
     *
     * @return string
     */
    public function getDefaultScope(): string
    {
        return $this->defaultScope ?: $this->currentSiteAccess->name;
    }

    /**
     * Sets a new default scope.
     *
     * @param string $scope
     */
    public function setDefaultScope($scope): void
    {
        $this->defaultScope = $scope;
    }

    public function setSiteAccess(SiteAccess $siteAccess = null): void
    {
        $this->currentSiteAccess = $siteAccess;
    }

    /**
     * Return true if give scope is a Site Access
     *
     * @param string $scope
     *
     * @return bool
     */
    protected function isSiteAccessScope(string $scope): bool
    {
        return $this->siteAccessProvider->isDefined($scope);
    }

    /**
     * Returns true if current config  provider supports given Site Access.
     *
     * @param SiteAccess $siteAccess
     * @return bool
     */
    protected function isSiteAccessSupported(SiteAccess $siteAccess): bool
    {
        return true;
    }

    protected function resolveScopeRelativeParamName($paramName, $namespace = null, $scope = null): string
    {
        return $this->getScopeRelativeParamName($paramName, ...$this->resolveNamespaceAndScope($namespace, $scope));
    }

    protected function resolveNamespaceAndScope($namespace = null, $scope = null): array
    {
        return [$namespace ?: $this->getDefaultNamespace(), $scope ?: $this->getDefaultScope()];
    }

    protected function getScopeRelativeParamName(string $paramName, string $namespace, string $scope): string
    {
        return "$namespace.$scope.$paramName";
    }

    protected abstract function doHasParameter(SiteAccess $siteAccess, string $paramName, string $namespace): bool;

    protected abstract function doGetParameter(SiteAccess $siteAccess, string $paramName, string $namespace);
}
