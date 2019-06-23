<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will attach Search Engine slots to SignalDispatcher.
 *
 * @deprecated Use {@see \eZ\Publish\Core\Base\Container\Compiler\Search\SearchEngineSignalSlotPass}
 */
class SignalSlotPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.signalslot.signal_dispatcher')) {
            return;
        }

        $signalDispatcherDef = $container->getDefinition('ezpublish.signalslot.signal_dispatcher');

        foreach ($container->findTaggedServiceIds('ezpublish.search.slot') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['signal'])) {
                    throw new LogicException(
                        "Could not find 'signal' attribute on '$id' service, " .
                        "which is mandatory for services tagged as 'ezpublish.persistence.solr.slot'"
                    );
                }

                $signalDispatcherDef->addMethodCall(
                    'attach',
                    [
                        $attribute['signal'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
