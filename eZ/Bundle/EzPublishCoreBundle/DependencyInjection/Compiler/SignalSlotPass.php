<?php

/**
 * File containing the SignalSlotPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register slots in SignalDispatcher.
 */
class SignalSlotPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.signalslot.signal_dispatcher')) {
            return;
        }

        $signalDispatcherDef = $container->getDefinition('ezpublish.signalslot.signal_dispatcher');
        foreach ($container->findTaggedServiceIds('ezpublish.api.slot') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['signal'])) {
                    throw new LogicException("Could not find 'signal' attribute on '$id' service, which is mandatory for services tagged as 'ezpublish.api.slot'");
                }

                $signalDispatcherDef->addMethodCall('attach', [$attribute['signal'], new Reference($id)]);
            }
        }
    }
}
