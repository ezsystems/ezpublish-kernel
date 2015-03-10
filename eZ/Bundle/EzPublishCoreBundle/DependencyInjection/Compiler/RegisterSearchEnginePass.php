<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

/**
 * This compiler pass will register eZ Publish search engines
 */
class RegisterSearchEnginePass implements CompilerPassInterface
{
    /**
     * Container service id of the SearchEngineFactory
     *
     * @see \eZ\Bundle\EzPublishCoreBundle\ApiLoader\SearchEngineFactory
     *
     * @var string
     */
    protected $factoryId;

    /**
     * Container tag used to mark a service as a search engine
     *
     * @var string
     */
    protected $searchEngineTag;

    /**
     * Construct from SearchEngineFactory service id and search engine services tag
     *
     * @see \eZ\Bundle\EzPublishCoreBundle\ApiLoader\SearchEngineFactory
     *
     * @param string $factoryId
     * @param string $searchEngineTag
     */
    public function __construct( $factoryId, $searchEngineTag )
    {
        $this->factoryId = $factoryId;
        $this->searchEngineTag = $searchEngineTag;
    }

    /**
     * Registers all found search engines to the SearchEngineFactory
     *
     * @throws \LogicException
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( $this->factoryId ) )
        {
            return;
        }

        $searchEngineFactoryDefinition = $container->getDefinition( $this->factoryId );

        foreach ( $container->findTaggedServiceIds( $this->searchEngineTag ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                {
                    throw new LogicException(
                        "'{$this->searchEngineTag}' service tag needs an 'alias' attribute to " .
                        "identify the search engine. None given."
                    );
                }

                // Register the search engine with the search engine factory
                $searchEngineFactoryDefinition->addMethodCall(
                    'registerSearchEngine',
                    array(
                        new Reference( $id ),
                        $attribute['alias'],
                    )
                );
            }
        }
    }
}
