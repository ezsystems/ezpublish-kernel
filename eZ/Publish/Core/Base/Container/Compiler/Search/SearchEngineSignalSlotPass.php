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
 */
class SearchEngineSignalSlotPass implements CompilerPassInterface
{
    /**
     * Name of a search engine for which Compiler Pass was instantiated.
     *
     * @var string
     */
    private $searchEngineAlias;

    public function __construct($searchEngineAlias)
    {
        $this->searchEngineAlias = $searchEngineAlias;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.signalslot.signal_dispatcher.factory')) {
            return;
        }

        $signalDispatcherFactoryDef = $container->getDefinition('ezpublish.signalslot.signal_dispatcher.factory');
        $searchEngineSlotTagName = sprintf('ezpublish.search.%s.slot', $this->searchEngineAlias);
        $tags = $container->findTaggedServiceIds('ezpublish.search.slot')
                + $container->findTaggedServiceIds($searchEngineSlotTagName);

        $searchEngineSignalSlots = [];
        foreach ($tags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['signal'])) {
                    throw new LogicException(
                        "Could not find 'signal' attribute on '$id' service, " .
                        "which is mandatory for services tagged as 'ezpublish.search.slot', " .
                        "or the specific tags for given search engine, in this case '$searchEngineSlotTagName'."
                    );
                }

                $searchEngineSignalSlots[$attribute['signal']][] = new Reference($id);
            }
        }

        $signalDispatcherFactoryDef->addMethodCall('addSlotsForSearchEngine', [$this->searchEngineAlias, $searchEngineSignalSlots]);
    }
}
