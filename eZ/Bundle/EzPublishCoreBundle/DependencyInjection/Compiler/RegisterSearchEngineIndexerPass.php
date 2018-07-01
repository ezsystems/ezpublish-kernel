<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

/**
 * This compiler pass registers eZ Publish search engines indexers.
 */
class RegisterSearchEngineIndexerPass implements CompilerPassInterface
{
    /**
     * Container service id of the SearchEngineIndexerFactory.
     *
     * @see \eZ\Bundle\EzPublishCoreBundle\ApiLoader\SearchEngineIndexerFactory
     *
     * @var string
     */
    protected $factoryId = 'ezpublish.api.search_engine.indexer.factory';

    /**
     * Register all found search engine indexers to the SearchEngineIndexerFactory.
     *
     * @throws \LogicException
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->factoryId)) {
            return;
        }

        $searchEngineIndexerFactoryDefinition = $container->getDefinition($this->factoryId);
        foreach ($container->findTaggedServiceIds('ezpublish.searchEngineIndexer') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        'ezpublish.searchEngineIndexer service tag needs an "alias" attribute to ' .
                        'identify the search engine. None given.'
                    );
                }

                // Register the search engine with the search engine factory
                $searchEngineIndexerFactoryDefinition->addMethodCall(
                    'registerSearchEngineIndexer',
                    [
                        new Reference($id),
                        $attribute['alias'],
                    ]
                );
            }
        }
    }
}
