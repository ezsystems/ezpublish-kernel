<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ContainerConfigBuilder;
use Symfony\Component\Config\Resource\ResourceInterface;

class PoliciesConfigBuilder extends ContainerConfigBuilder
{
    public function addConfig(array $config)
    {
        $policyMap = [];
        if ($this->containerBuilder->hasParameter('ezpublish.api.role.policy_map')) {
            $policyMap = $this->containerBuilder->getParameter('ezpublish.api.role.policy_map');
        }

        $this->containerBuilder->setParameter(
            'ezpublish.api.role.policy_map',
            array_merge_recursive($policyMap, $config)
        );
    }

    public function addResource(ResourceInterface $resource)
    {
        $this->containerBuilder->addResource($resource);
    }
}
