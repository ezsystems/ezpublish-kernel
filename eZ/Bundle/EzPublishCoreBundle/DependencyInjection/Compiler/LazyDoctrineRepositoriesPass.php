<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class LazyDoctrineRepositoriesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $nonLazyServices = [];
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (!is_array($definition->getFactory())) {
                continue;
            }

            $factory = $definition->getFactory();
            if (!is_string($factory[0]) && !$factory[0] instanceof Reference) {
                continue;
            }

            $factoryServiceId = (string) $factory[0];

            if ($factoryServiceId !== 'ibexa.doctrine.orm.entity_manager') {
                continue;
            }

            if ($definition->isLazy()) {
                continue;
            }

            $nonLazyServices[] = $serviceId;
        }

        if (empty($nonLazyServices)) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                'Services: "%s" have a dependency on repository aware Entity Manager. '
                . 'To prevent premature Entity Manager initialization before siteaccess is resolved '
                . "you need to mark these services as 'lazy'.",
                implode('", "', $nonLazyServices)
            )
        );
    }
}
