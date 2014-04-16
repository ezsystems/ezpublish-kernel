<?php
/**
 * File containing the FieldTypePass class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Legacy Storage role limitation converters.
 */
class RoleLimitationConverterPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.persistence.legacy.role.limitation.converter' ) )
        {
            return;
        }

        $roleLimitationConverter = $container->getDefinition( 'ezpublish.persistence.legacy.role.limitation.converter' );

        foreach ( $container->findTaggedServiceIds( 'ezpublish.persistence.legacy.role.limitation.handler' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                $roleLimitationConverter->addMethodCall(
                    'addHandler',
                    array( new Reference( $id ) )
                );
            }
        }
    }
}
