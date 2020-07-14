<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Publish\Core\Base\Container\Compiler\TaggedServiceIdsIterator\BackwardCompatibleIterator;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass registers eZ Publish search engines indexers.
 */
class RegisterSearchEngineIndexerPass implements CompilerPassInterface
{
    public const SEARCH_ENGINE_INDEXER_SERVICE_TAG = 'ezplatform.search_engine.indexer';
    public const DEPRECATED_SEARCH_ENGINE_INDEXER_SERVICE_TAG = 'ezpublish.searchEngineIndexer';

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

        $iterator = new BackwardCompatibleIterator(
            $container,
            self::SEARCH_ENGINE_INDEXER_SERVICE_TAG,
            self::DEPRECATED_SEARCH_ENGINE_INDEXER_SERVICE_TAG
        );

        foreach ($iterator as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            'Service "%s" tagged with "%s" or "%s" needs an "alias" attribute to identify the search engine',
                            $serviceId,
                            self::SEARCH_ENGINE_INDEXER_SERVICE_TAG,
                            self::DEPRECATED_SEARCH_ENGINE_INDEXER_SERVICE_TAG
                        )
                    );
                }

                // Register the search engine with the search engine factory
                $searchEngineIndexerFactoryDefinition->addMethodCall(
                    'registerSearchEngineIndexer',
                    [
                        new Reference($serviceId),
                        $attribute['alias'],
                    ]
                );
            }
        }
    }
}
