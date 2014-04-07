<?php
/**
 * File containing the FieldTypeRepositoryPass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register eZ Publish field types.
 */
class FieldTypeRepositoryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.api.repository.factory' ) )
            return;

        $repositoryFactoryDef = $container->getDefinition( 'ezpublish.api.repository.factory' );

        // Field types.
        // Alias attribute is the field type string.
        foreach ( $container->findTaggedServiceIds( 'ezpublish.fieldType' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new \LogicException( 'ezpublish.fieldType service tag needs an "alias" attribute to identify the field type. None given.' );

                $repositoryFactoryDef->addMethodCall(
                    'registerFieldType',
                    array(
                        // Only pass the service Id since field types will be lazy loaded via the service container
                        $id,
                        $attribute['alias']
                    )
                );
            }
        }
    }
}
