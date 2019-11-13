<?php

declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\StaticSiteAccessProvider;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @property-read \Symfony\Component\DependencyInjection\ContainerInterface $container
 */
class StaticSiteAccessConfigResolver extends SiteAccessConfigResolver
{
    use ContainerAwareTrait;

    protected function doHasParameter(SiteAccess $siteAccess, string $paramName, string $namespace): bool
    {
        return $this->container->hasParameter(
            $this->resolveScopeRelativeParamName($paramName, $namespace, $siteAccess->name)
        );
    }

    protected function doGetParameter(SiteAccess $siteAccess, string $paramName, string $namespace)
    {
        $scopeRelativeParamName = $this->getScopeRelativeParamName($paramName, $namespace, $siteAccess->name);
        if ($this->container->hasParameter($scopeRelativeParamName)) {
            return $this->container->getParameter($scopeRelativeParamName);
        }

        throw new ParameterNotFoundException($paramName, $namespace, [$siteAccess->name]);
    }

    protected function isSiteAccessSupported(SiteAccess $siteAccess): bool
    {
        return StaticSiteAccessProvider::class === $siteAccess->provider;
    }
}
