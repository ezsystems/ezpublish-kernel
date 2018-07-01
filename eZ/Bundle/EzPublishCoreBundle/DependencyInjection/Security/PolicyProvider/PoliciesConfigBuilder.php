<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ContainerConfigBuilder;
use Symfony\Component\Config\Resource\ResourceInterface;

class PoliciesConfigBuilder extends ContainerConfigBuilder
{
    public function addConfig(array $config)
    {
        $previousPolicyMap = [];

        if ($this->containerBuilder->hasParameter('ezpublish.api.role.policy_map')) {
            $previousPolicyMap = $this->containerBuilder->getParameter('ezpublish.api.role.policy_map');
        }

        // We receive limitations as values, but we want them as keys to be used by isset().
        foreach ($config as $module => $functionArray) {
            foreach ($functionArray as $function => $limitationCollection) {
                if (null !== $limitationCollection && $this->policyExists($previousPolicyMap, $module, $function)) {
                    $limitations = array_merge_recursive($previousPolicyMap[$module][$function], array_fill_keys((array)$limitationCollection, true));
                } else {
                    $limitations = array_fill_keys((array)$limitationCollection, true);
                }

                $previousPolicyMap[$module][$function] = $limitations;
            }
        }

        $this->containerBuilder->setParameter(
            'ezpublish.api.role.policy_map',
            $previousPolicyMap
        );
    }

    public function addResource(ResourceInterface $resource)
    {
        $this->containerBuilder->addResource($resource);
    }

    /**
     * Checks if policy for module and function exist in Policy Map.
     *
     * @param array $policyMap
     * @param string $module
     * @param string $function
     *
     * @return bool
     */
    private function policyExists(array $policyMap, $module, $function)
    {
        return array_key_exists($module, $policyMap) && array_key_exists($function, $policyMap[$module]);
    }
}
