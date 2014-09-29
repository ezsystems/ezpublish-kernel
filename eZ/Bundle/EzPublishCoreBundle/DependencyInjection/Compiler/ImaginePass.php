<?php
/**
 * File containing the ImaginePass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImaginePass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'liip_imagine.filter.configuration' ) )
        {
            return;
        }

        $filterConfigDef = $container->findDefinition( 'liip_imagine.filter.configuration' );
        $filterConfigDef->addMethodCall( 'setConfigResolver', array( new Reference( 'ezpublish.config.resolver' ) ) );
    }
}
