<?php
/**
 * File containing the HttpCachePass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class HttpCachePass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.http_cache.cache_manager' ) )
        {
            return;
        }

        if ( !$container->hasDefinition( 'fos_http_cache.proxy_client.varnish' ) )
        {
            throw new InvalidArgumentException( 'Varnish proxy client must be enabled in FOSHttpCacheBundle' );
        }

        $varnishClientDef = $container->findDefinition( 'fos_http_cache.proxy_client.varnish' );
        $varnishClientDef
            ->setFactoryService( 'ezpublish.http_cache.proxy_client.varnish.factory' )
            ->setFactoryMethod( 'buildProxyClient' );

        // Forcing cache manager to use Varnish proxy client, for BAN support.
        $cacheManagerDef = $container->findDefinition( 'ezpublish.http_cache.cache_manager' );
        $cacheManagerDef->replaceArgument( 0, new Reference( 'fos_http_cache.proxy_client.varnish' ) );
    }
}
