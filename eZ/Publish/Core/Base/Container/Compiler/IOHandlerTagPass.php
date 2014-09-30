<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\Base\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;

/**
 * Registers IO handlers tagged as ezpublish.io_handler
 */
class IOHandlerTagPass implements CompilerPassInterface
{
    /**
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'ezpublish.core.io.factory' ) )
            return;

        $ioHandlersMap = array();
        foreach ( $container->findTaggedServiceIds( 'ezpublish.io_handler' ) as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute['alias'] ) )
                    throw new LogicException(
                        'ezpublish.io_handler service tag needs an "alias" attribute to identify the handler.'
                    );

                $ioHandlersMap[$attribute['alias']] = $id;
            }
        }

        $container->getDefinition( 'ezpublish.core.io.factory' )
            ->addMethodCall( 'setHandlersMap', array( $ioHandlersMap ) );
    }
}
