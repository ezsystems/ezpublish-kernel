<?php

/**
 * File containing the HttpCachePass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * HttpCache related compiler pass.
 *
 * Ensures Varnish proxy client is correctly configured.
 *
 * @deprecated replaced by ezplatform-http-cache package, will be removed in future 7.x FT release.
 */
class HttpCachePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->processCacheManager($container);
        $this->processPurgeClient($container);
    }

    private function processCacheManager(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.http_cache.cache_manager')) {
            return;
        }

        if (!$container->hasDefinition('fos_http_cache.proxy_client.varnish')) {
            throw new InvalidArgumentException('Varnish proxy client must be enabled in FOSHttpCacheBundle');
        }

        $varnishClientDef = $container->findDefinition('fos_http_cache.proxy_client.varnish');
        $varnishClientDef->setFactory(
            [
                new Reference('ezpublish.http_cache.proxy_client.varnish.factory'),
                'buildProxyClient',
            ]
        );
        // Set it lazy as it can be loaded during cache warming and factory depends on ConfigResolver while cache warming
        // occurs before SA matching.
        $varnishClientDef->setLazy(true);

        // Forcing cache manager to use Varnish proxy client, for BAN support.
        $cacheManagerDef = $container->findDefinition('ezpublish.http_cache.cache_manager');
        $cacheManagerDef->replaceArgument(0, new Reference('fos_http_cache.proxy_client.varnish'));
    }

    private function processPurgeClient(ContainerBuilder $container)
    {
        // Check that alias exists (if not it has been removed by another bundle)
        if (!$container->has('ezpublish.http_cache.purge_client')) {
            return;
        }

        $purgeType = $container->getParameter('ezpublish.http_cache.purge_type');
        switch ($purgeType) {
            case 'local':
                $purgeService = 'ezpublish.http_cache.purge_client.local';
                break;
            case 'http':
                $purgeService = 'ezpublish.http_cache.purge_client.fos';
                break;
            default:
                if (!$container->has($purgeType)) {
                    throw new InvalidArgumentException("Invalid ezpublish.http_cache.purge_type. Can be 'local', 'http' or a valid service identifier implementing PurgeClientInterface.");
                } elseif (!$container->get($purgeType) instanceof PurgeClientInterface) {
                    throw new InvalidArgumentException('Invalid ezpublish.http_cache.purge_type, it needs to implement PurgeClientInterface.');
                }

                $purgeService = $purgeType;
        }

        $container->setAlias('ezpublish.http_cache.purge_client', $purgeService);
    }
}
