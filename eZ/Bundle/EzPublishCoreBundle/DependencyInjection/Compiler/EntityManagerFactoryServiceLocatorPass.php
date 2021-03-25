<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class EntityManagerFactoryServiceLocatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $entityManagerFactory = $container->getDefinition('ibexa.doctrine.orm.entity_manager_factory');

        $ibexaEntityManagers = $this->getIbexaEntityManagers($container);
        $entityManagerFactory->setArgument(
            '$serviceLocator',
            ServiceLocatorTagPass::register($container, $ibexaEntityManagers)
        );
    }

    private function getIbexaEntityManagers(ContainerBuilder $container): array
    {
        $entityManagers = [];
        foreach ($container->getParameter('doctrine.entity_managers') as $name => $serviceId) {
            if (false === strpos($name, 'ibexa_')) {
                continue;
            }

            $entityManagers[$serviceId] = new Reference($serviceId);
        }

        return $entityManagers;
    }
}
