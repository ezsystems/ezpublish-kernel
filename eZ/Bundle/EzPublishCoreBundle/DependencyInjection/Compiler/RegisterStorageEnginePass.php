<?php
/**
 * File containing the RegisterStorageEnginePass class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass will register eZ Publish field types.
 */
class RegisterStorageEnginePass implements CompilerPassInterface
{

    /**
     * Performs compiler passes for persistence factories
     *
     * Does:
     * - Registers all storage engines to ezpublish.api.storage_engine.factory
     * - Sets the default storage engine id to %ezpublish.spi.persistence.default_id%
     *   as used by ezpublish.spi.persistence.lazy_factory
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.api.storage_engine.factory' ) )
            return;

        $default = $container->getParameter( 'ezpublish.api.storage_engine.default' );
        $storageEngineFactoryDef = $container->getDefinition( 'ezpublish.api.storage_engine.factory' );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.storageEngine' ) as $id => $attributes )
        {
            // Set the default id on parameter ezpublish.spi.persistence.default_id for lazy factory
            if ( $attributes[0]['alias'] === $default )
                $container->setParameter( 'ezpublish.spi.persistence.default_id', $id );

            // Register the storage engine on the main storage engine factory
            $storageEngineFactoryDef->addMethodCall(
                'registerStorageEngine',
                array(
                    $id,
                    // @todo: Maybe there should be some validation here. What if no alias is provided ?
                    $attributes[0]['alias']
                )
            );
        }
    }
}
