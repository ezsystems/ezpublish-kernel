<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @property-read \Symfony\Component\DependencyInjection\ContainerInterface $container
 *
 * @internal
 */
class SiteAccessGroupConfigResolver extends SiteAccessConfigResolver
{
    use ContainerAwareTrait;

    protected function doHasParameter(SiteAccess $siteAccess, string $paramName, string $namespace): bool
    {
        foreach ($siteAccess->groups as $group) {
            $groupScopeParamName = $this->resolveScopeRelativeParamName($paramName, $namespace, $group->getName());
            if ($this->container->hasParameter($groupScopeParamName)) {
                return true;
            }
        }

        return false;
    }

    protected function doGetParameter(SiteAccess $siteAccess, string $paramName, string $namespace)
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
}
