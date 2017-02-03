<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Processes services tagged as ezplatform.cache_response_tagger, and registers them with the dispatcher.
 */
class ResponseTaggersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezplatform.view_cache.response_tagger.dispatcher')) {
            return;
        }

        $taggers = [];

        $taggedServiceIds = $container->findTaggedServiceIds('ezplatform.cache_response_tagger');
        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            $taggers[] = new Reference($taggedServiceId);
        }

        $dispatcher = $container->getDefinition('ezplatform.view_cache.response_tagger.dispatcher');
        $dispatcher->replaceArgument(0, $taggers);
    }
}
