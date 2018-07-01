<?php

/**
 * File containing the RouterPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Routing related compiler pass.
 *
 * Manipulates Symfony default router services to adapt them to eZ routing needs,
 * specifically to implement the RequestMatcherInterface.
 */
class RouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('router.default')) {
            return;
        }

        $container
            ->findDefinition('router.default')
            ->setClass(DefaultRouter::class);
    }
}
