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
 * This compiler pass will register eZ Publish search engines.
 */
class RegisterSearchEnginePass implements CompilerPassInterface
{
    public const SEARCH_ENGINE_SERVICE_TAG = 'ezplatform.search_engine';
    public const DEPRECATED_SEATCH_ENGINE_SERVICE_TAG = 'ezpublish.searchEngine';

    /**
     * Container service id of the SearchEngineFactory.
     *
     * @see \eZ\Bundle\EzPublishCoreBundle\ApiLoader\SearchEngineFactory
     *
     * @var string
     */
    protected $factoryId = 'ezpublish.api.search_engine.factory';

    /**
     * Registers all found search engines to the SearchEngineFactory.
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

        $searchEngineFactoryDefinition = $container->getDefinition($this->factoryId);

        $iterator = new BackwardCompatibleIterator(
            $container,
            self::SEARCH_ENGINE_SERVICE_TAG,
            self::DEPRECATED_SEATCH_ENGINE_SERVICE_TAG
        );

        foreach ($iterator as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(
                        sprintf(
                            'Service "%s" tagged with "%s" or "%s" needs an "alias" attribute to identify the search engine',
                            $serviceId,
                            self::SEARCH_ENGINE_SERVICE_TAG,
                            self::DEPRECATED_SEATCH_ENGINE_SERVICE_TAG
                        )
                    );
                }

                // Register the search engine with the search engine factory
                $searchEngineFactoryDefinition->addMethodCall(
                    'registerSearchEngine',
                    [
                        new Reference($serviceId),
                        $attribute['alias'],
                    ]
                );
            }
        }
    }
}
